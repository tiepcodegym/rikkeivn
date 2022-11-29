<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Permission as PermissModel;
use Rikkei\Team\Model\Action;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Carbon\Carbon;

class WorkingTime extends CoreModel
{
    protected $table = 'working_times';
    protected $fillable = [
        'parent_id',
        'employee_id',
        'team_id',
        'approver_id',
        'status',
        'from_month',
        'to_month',
        'start_time1',
        'end_time1',
        'start_time2',
        'end_time2',
        'related_ids',
        'reason',
        'created_by'
    ];
    const NOT_APPROVE_FIELD = ['approver_id', 'related_ids', 'reason'];
    const APPROVED_FIELD = ['from_month', 'to_month', 'start_time1', 'end_time1', 'start_time2', 'end_time2'];
    const ROUTE_REGISTER = 'manage_time::permiss.wktime.register';
    const ROUTE_MANAGE = 'manage_time::permis.wktime.manage';
    const ROUTE_APPROVE = 'manage_time::permis.wktime.approve';
    const ROUTE_LOG_TIME = 'manage_time::permiss.log_time';
    const ACTION_APPROVE = 'working_time.approve';

    /*
     * get employee that belongs to
     */
    public function employee()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id');
    }

    /*
     * get approver that belongs to
     */
    public function approver()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'approver_id');
    }

    /*
     * get team that belongs to
     */
    public function team()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Team', 'team_id');
    }

    /*
     * get team code of item
     */
    public static function getTeamCode($item = null, $employee = null)
    {
        if (!$employee) {
            $employee = Permission::getInstance()->getEmployee();
        }
        $team = $item ? $item->team : null;
        if (!$team) {
            $team = $employee->newestTeam();
        } else {
            $team = Team::getFirstHasCode($team);
        }
        return explode('_', $team->code)[0];
    }

    /*
     * set related ids
     */
    public function setRelatedIdsAttribute($value)
    {
        if (is_array($value)) {
            if ($value) {
                $value = json_encode($value);
            } else {
                $value = null;
            }
        }
        $this->attributes['related_ids'] = $value;
    }

    /*
     * set from_month attribute
     */
    public function setFromMonthAttribute($value)
    {
        try {
            $month = Carbon::createFromFormat('m-Y', $value)->startOfMonth()->toDateString();
        } catch (\Exception $ex) {
            $month = $value;
        }
        $this->attributes['from_month'] = $month;
    }

    /*
     * set to_month attribute
     */
    public function setToMonthAttribute($value)
    {
        try {
            $month = Carbon::createFromFormat('m-Y', $value)->startOfMonth()->toDateString();
        } catch (\Exception $ex) {
            $month = $value;
        }
        $this->attributes['to_month'] = $month;
    }

    /*
     * get from_month attribute
     */
    public function getFromMonth()
    {
        return Carbon::parse($this->from_month)->format('m-Y');
    }

    /*
     * get to_month attribute
     */
    public function getToMonth()
    {
        return Carbon::parse($this->to_month)->format('m-Y');
    }

    /*
     * get related ids (array)
     */
    public function getRelatedIds()
    {
        if (!$this->related_ids) {
            return [];
        }
        return json_decode($this->related_ids, true);
    }

    /*
     * get related people
     */
    public function getRelated()
    {
        $relatedIds = $this->getRelatedIds();
        if (!$relatedIds) {
            return null;
        }
        return Employee::select('id', 'name', 'email')->whereIn('id', $relatedIds)->get();
    }

    /*
     * check item deleteable
     */
    public function delable()
    {
        return $this->status != MTConst::STT_WK_TIME_APPROVED;
    }

    /*
     * check permission register
     */
    public static function permissRegister()
    {
        if (!Permission::getInstance()->isAllow(self::ROUTE_REGISTER)) {
            CoreView::viewErrorPermission();
        }
    }

    /*
     * get register permission
     */
    public static function getPermisison($item = null)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        $scope = Permission::getInstance();
        $route = self::ROUTE_MANAGE;
        $permissEdit = false; //quyền sửa/lưu lại
        $permissView = false; //quyền xem
        $permissApprove = $scope->isAllow(self::ROUTE_APPROVE); // quyền approve
        $permissNotApprove = $permissApprove; //không duyệt

        if (!$item) { //không có tài liệu
            $permissEdit = true;
            $permissView = true;
        } else {
            //permission scope
            if ($scope->isScopeCompany(null, $route)) {
                $permissEdit = true;
                $permissApprove = true;
            } elseif ($scope->isScopeTeam(null, $route)) {
                $wktTbl = self::getTableName();
                $tmbTbl = TeamMember::getTableName();
                $teamIds = TeamMember::where('employee_id', $currentUser->id)
                        ->lists('team_id')
                        ->toArray();
                $teamIds = Team::teamChildIds($teamIds);
                //has item edit
                $hasItem = self::from($wktTbl . ' as wktime')
                        ->join($tmbTbl . ' as tmb', 'wktime.employee_id', '=', 'tmb.employee_id')
                        ->where(function ($query) use ($teamIds, $currentUser) {
                            $query->whereIn('tmb.team_id', $teamIds)
                                    ->orWhere('wktime.employee_id', '=', $currentUser->id)
                                    ->orWhere('wktime.created_by', '=', $currentUser->id);
                        })
                        ->where('wktime.id', $item->id)
                        ->first();
                $permissEdit = $hasItem != null;
                //has item approve
                $hasItemApprove = self::from($wktTbl . ' as wktime')
                        ->join($tmbTbl . ' as tmb', 'wktime.employee_id', '=', 'tmb.employee_id')
                        ->where(function ($query) use ($teamIds, $currentUser) {
                            $query->whereIn('tmb.team_id', $teamIds)
                                    ->orWhere('wktime.approver_id', '=', $currentUser->id);
                        })
                        ->where('wktime.id', $item->id)
                        ->first();
                $permissApprove = $hasItemApprove != null;
            } else {
                $permissEdit = in_array($currentUser->id, [$item->employee_id, $item->created_by]);
                $permissApprove = $currentUser->id == $item->approver_id;
            }

            $permissNotApprove = $permissApprove;
            $relatedIds = $item->getRelatedIds();
            $permissView = $permissEdit || $permissApprove || in_array($currentUser->id, $relatedIds);
        }

        if ($item && $item->status == MTConst::STT_WK_TIME_APPROVED &&
                !in_array($currentUser->id, [$item->employee_id, $item->created_by])) {
            $permissEdit = false;
        }

        return [
            'view' => $permissView,
            'edit' => $permissEdit,
            'approve' => $permissApprove,
            'not_approve' => $permissNotApprove
        ];
    }

    /*
     * has other register time
     */
    public static function hasOtherTimeRegister($item)
    {
        $currMonth = Carbon::now()->startOfMonth()->toDateString();
        if ($currMonth > $item->to_month) {
            return ['status' => true, 'list' => []];
        }
        $fromMonth = Carbon::parse($item->from_month)->startOfMonth()->toDateTimeString();
        $toMonth = Carbon::parse($item->to_month)->endOfMonth()->toDateTimeString();
        $tblOt = OtRegister::getTableName();
        $tblOtEmp = \Rikkei\Ot\Model\OtEmployee::getTableName();
        $hasOt = OtRegister::select($tblOt . '.*')
                ->join($tblOtEmp . ' as ot_emp', $tblOt . '.id', '=', 'ot_emp.ot_register_id')
                ->where('ot_emp.employee_id', $item->employee_id)
                ->whereNotIn($tblOt.'.status', [OtRegister::REJECT, OtRegister::REMOVE])
                ->where($tblOt.'.start_at', '<=', $toMonth)
                ->where($tblOt.'.end_at', '>=', $fromMonth)
                ->groupBy($tblOt . '.id')
                ->get();
        $hasLeaveDay = LeaveDayRegister::where('creator_id', $item->employee_id)
                ->whereNotIn('status', [LeaveDayRegister::STATUS_CANCEL, LeaveDayRegister::STATUS_DISAPPROVE])
                ->where('date_start', '<=', $toMonth)
                ->where('date_end', '>=', $fromMonth)
                ->get();
        $hasSupplement = SupplementRegister::where('creator_id', $item->employee_id)
                ->whereNotIn('status', [SupplementRegister::STATUS_CANCEL, SupplementRegister::STATUS_DISAPPROVE])
                ->where('date_start', '<=', $toMonth)
                ->where('date_end', '>=', $fromMonth)
                ->get();
        $tblBusiness = BusinessTripRegister::getTableName();
        $tblBusinessEmp = \Rikkei\ManageTime\Model\BusinessTripEmployee::getTableName();
        $hasBusiness = BusinessTripRegister::select($tblBusiness . '.*')
                ->join($tblBusinessEmp, $tblBusiness . '.id', '=', $tblBusinessEmp . '.register_id')
                ->where($tblBusinessEmp . '.employee_id', $item->employee_id)
                ->whereNotIn($tblBusiness . '.status', [BusinessTripRegister::STATUS_CANCEL, BusinessTripRegister::STATUS_DISAPPROVE])
                ->where($tblBusiness . '.date_start', '<=', $toMonth)
                ->where($tblBusiness . '.date_end', '>=', $fromMonth)
                ->groupBy($tblBusiness . '.id')
                ->get();
        return [
            'status' => !$hasOt->isEmpty() || !$hasLeaveDay->isEmpty() || !$hasSupplement->isEmpty() || !$hasBusiness->isEmpty(),
            'list' => [
                'ot' => $hasOt,
                'leave_day' => $hasLeaveDay,
                'supplement' => $hasSupplement,
                'business' => $hasBusiness
            ]
        ];
    }

    /*
     * check permiss approve
     */
    public static function isPermissApprove()
    {
        return Permission::getInstance()->isAllow(self::ROUTE_APPROVE);
    }

    public function draftItem()
    {
        return self::where('parent_id', $this->id)->first();
    }

    /*
     * check change field
     */
    public function isChangeData($data)
    {
        $oldData = array_only($this->getAttributes(), static::APPROVED_FIELD);
        $data = array_only($data, static::APPROVED_FIELD);
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $oldData)) {
                if (in_array($key, ['from_month', 'to_month'])) {
                    $value = Carbon::createFromFormat('m-Y', $value)->startOfMonth()->toDateString();
                }
                if ($value != $oldData[$key]) {
                    return true;
                }
            }
        }
        return false;
    }

    /*
     * insert or update item
     */
    public static function insertOrUpdate($data = [], $itemData = null)
    {
        $currUser = Permission::getInstance()->getEmployee();
        $currUserId = $currUser->id;
        $data['employee_id'] = $currUserId;
        if (!isset($data['related_ids'])) {
            $data['related_ids'] = null;
        }
        unset($data['status']);
        $isChangeData = true;
        $isEdit = isset($data['id']);
        $oldItem = null;
        if ($isEdit) {
            $item = $itemData ? $itemData : self::findOrFail($data['id']);
            $oldItem = clone $item;
            $isUpdated = false;
            $isChangeData = $item->isChangeData($data);
            //update file not approve
            $dataNotApprove = array_only($data, self::NOT_APPROVE_FIELD);
            //if approved then create draft item
            if ($isChangeData) {
                $dataNotApprove['status'] = MTConst::STT_WK_TIME_NOT_APPROVE;
                $draftItem = $item->draftItem();
                //not have draft item
                if (!$draftItem) {
                    //if item approved then create draft item else update item
                    if (in_array($item->status, [MTConst::STT_WK_TIME_APPROVED, MTConst::STT_WK_TIME_REJECT])) {
                        $data['parent_id'] = $item->id;
                        $data['created_by'] = $currUserId;
                        $newestTeam = $currUser->newestTeam();
                        $data['team_id'] = $newestTeam ? $newestTeam->id : null;
                        self::create($data);
                    } else {
                        $isUpdated = true;
                        $item->update($data);
                    }
                } else {
                    $isChangeData = $draftItem->isChangeData($data);
                    $draftItem->update($data);
                }
            }
            if (!$isUpdated) {
                $item->update($dataNotApprove);
            }
        } else {
            $data['created_by'] = $currUserId;
            $newestTeam = $currUser->newestTeam();
            $data['team_id'] = $newestTeam ? $newestTeam->id : null;
            $item = self::create($data);
        }

        //if change data or change approver
        if ($isChangeData || ($isEdit && $oldItem->approver_id != $item->approver_id)) {
            self::afterUpdateItem($item, $isEdit);
        } elseif ($oldItem) {
            $oldRelatedIds = $oldItem->getRelatedIds();
            $newRelatedIds = $item->getRelatedIds();
            if ($relatedIds = array_diff($newRelatedIds, $oldRelatedIds)) {
                $listRelateds = Employee::select('id', 'name', 'email')->whereIn('id', $relatedIds)->get();
                self::mailChangeRelated($item, $listRelateds);
            }
        } else {
            //none
        }

        return $item;
    }

    /*
     * action after update or create item
     */
    public static function afterUpdateItem($item, $isUpdate)
    {
        $approver = $item->approver;
        $employee = $item->employee;
        if (!$approver || !$employee) {
            return false;
        }

        $relateds = $item->getRelated();
        $detailLink = route('manage_time::wktime.register', ['id' => $item->id]);
        $fromMonth = $item->getFromMonth();
        $toMonth = $item->getToMonth();
        if ($fromMonth == $toMonth) {
            $month = $fromMonth;
        } else {
            $month = '('. $fromMonth . ' ' . trans('manage_time::view.time_to') . ' ' . $toMonth . ')';
        }
        $account = CoreView::getNickName($employee->email);
        $subject = trans('manage_time::view.mail_subject_working_time_submit', ['name' => $account, 'month' => $month]);
        $subject .= ' ' . ($isUpdate ? trans('manage_time::view.was_updated') : trans('manage_time::view.was_created'));
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($approver->email)
                ->setSubject($subject)
                ->setTemplate('manage_time::working-time.mails.submit-form', [
                    'dearName' => $approver->name,
                    'employeeName' => $employee->name,
                    'employeeAccount' => $account,
                    'detailLink' => $detailLink,
                    'fromMonth' => $fromMonth,
                    'toMonth' => $toMonth,
                    'isUpdate' => $isUpdate
                ])
                ->setNotify($approver->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
                ->addCcRelated($relateds)
                ->save();
    }

    /*
     * send mail to related added
     */
    public static function mailChangeRelated($item, $relateds)
    {
        $employee = $item->employee;
        if (!$relateds || $relateds->isEmpty() || !$employee) {
            return;
        }
        $detailLink = route('manage_time::wktime.register', ['id' => $item->id]);
        $fromMonth = $item->getFromMonth();
        $toMonth = $item->getToMonth();
        if ($fromMonth == $toMonth) {
            $month = $fromMonth;
        } else {
            $month = '('. $fromMonth . ' ' . trans('manage_time::view.time_to') . ' ' . $toMonth . ')';
        }
        $account = CoreView::getNickName($employee->email);
        $subject = trans('manage_time::view.mail_subject_related_working_time', ['name' => $account, 'month' => $month]);
        $subject .= ' ' . trans('manage_time::view.was_updated');
        foreach ($relateds as $emp) {
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($emp->email)
                    ->setSubject($subject)
                    ->setTemplate('manage_time::working-time.mails.submit-form', [
                        'dearName' => $emp->name,
                        'employeeName' => $employee->name,
                        'employeeAccount' => $account,
                        'detailLink' => $detailLink,
                        'fromMonth' => $fromMonth,
                        'toMonth' => $toMonth,
                        'isUpdate' => true
                    ])
                    ->setNotify($emp->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
                    ->save();
        }
    }

    /*
     * update status item
     */
    public static function updateStatusItem($item, $status)
    {
        $employee = $item->employee;
        if ($status != MTConst::STT_WK_TIME_NOT_APPROVE && ($item->status == $status || !$employee)) {
            return false;
        }

        $saveStatus = false;
        //check if approve
        if ($status == MTConst::STT_WK_TIME_APPROVED) {
            $itemDraft = $item->draftItem();
            if ($itemDraft) {
                $draftAttribute = $itemDraft->getAttributes();
                $approvedData = array_only($draftAttribute, self::APPROVED_FIELD);
                $approvedData['status'] = $status;
                $item->update($approvedData);
                $itemDraft->delete();
                $saveStatus = true;
            }
        }
        //save status
        if (!$saveStatus) {
            $item->status = $status;
            $item->save();
        }
        //send email
        $statusText = self::getTextStatus($status);
        $detailLink = route('manage_time::wktime.register', ['id' => $item->id]);
        $fromMonth = $item->getFromMonth();
        $toMonth = $item->getToMonth();
        if ($fromMonth == $toMonth) {
            $month = $fromMonth;
        } else {
            $month = '('. $fromMonth . ' ' . trans('manage_time::view.time_to') . ' ' . $toMonth . ')';
        }
        $subject = trans('manage_time::view.mail_subject_working_time_approve', ['month' => $month, 'status' => $statusText]);
        //email to register
        $dataMail = [
            'dearName' => $employee->name,
            'content' => $subject,
            'detailLink' => $detailLink,
            'fromMonth' => $fromMonth,
            'toMonth' => $toMonth
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($employee->email)
                ->setSubject($subject)
                ->setTemplate('manage_time::working-time.mails.approve-form', $dataMail)
                ->setNotify($employee->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
                ->save();
        //email to related
        $relateds = $item->getRelated();
        if ($relateds && !$relateds->isEmpty()) {
            $relatedSubject = trans('manage_time::view.mail_subject_working_time_approve_related', [
                'name' => CoreView::getNickName($employee->email),
                'month' => $month,
                'status' => $statusText
            ]);
            foreach ($relateds as $relate) {
                $dataMail['dearName'] = $relate->name;
                $dataMail['content'] = $relatedSubject . ', ' . trans('manage_time::view.related_to_you');
                $emailRelated = new EmailQueue();
                $emailRelated->setTo($relate->email)
                        ->setSubject($relatedSubject)
                        ->setTemplate('manage_time::working-time.mails.approve-form', $dataMail)
                        ->setNotify($relate->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_TIMEKEEPING])
                        ->save();
            }
        }
    }

    /*
     * render text status
     */
    public static function getTextStatus($status)
    {
        switch ($status) {
            case MTConst::STT_WK_TIME_NOT_APPROVE:
                return trans('manage_time::view.was_unapproved');
            case MTConst::STT_WK_TIME_APPROVED:
                return trans('manage_time::view.was_approved');
            case MTConst::STT_WK_TIME_REJECT:
                return trans('manage_time::view.was_rejected');
            default:
                return null;
        }
    }

    /*
     * check validate total working time
     */
    public static function validTotalTime($data)
    {
        if (!isset($data['start_time1'])) {
            return ['status' => true];
        }
        $timeStart1 = Carbon::parse($data['start_time1']);
        $timeEnd1 = Carbon::parse($data['end_time1']);
        $timeStart2 = Carbon::parse($data['start_time2']);
        $timeEnd2 = Carbon::parse($data['end_time2']);
        $timeDiff1 = $timeEnd1->diff($timeStart1);
        $timeDiff2 = $timeEnd2->diff($timeStart2);
        $defaultTimes = CoreConfigData::getValueDb(MTConst::KEY_RANGE_WKTIME);
        $defaultTimes = $defaultTimes ? unserialize($defaultTimes) : ['min_mor' => 4, 'min_aft' => 3];
        $minMor = floatval($defaultTimes['min_mor']);
        $minAft = floatval($defaultTimes['min_aft']);
        if (($timeDiff1->h + $timeDiff1->i / 60) < $minMor || ($timeDiff2->h + $timeDiff2->i / 60) < $minAft) {
            return [
                'status' => false,
                'message' => trans('manage_time::message.invalid_shift_time', ['mor' => $minMor, 'aft' => $minAft])
            ];
        }
        $totalMinute = ($timeDiff1->h + $timeDiff2->h) * 60 + $timeDiff1->i + $timeDiff2->i;
        if ($totalMinute / 60 == MTConst::TOTAL_WORKING_TIME && $totalMinute % 60 == 0) {
            return ['status' => true];
        }
        return [
            'status' => false,
            'message' => trans('manage_time::message.total_working_time_invalid')
        ];
    }

    /*
     * check valid exists month
     */
    public static function validExistMonth($data, $empId = null)
    {
        if (!$empId) {
            $empId = Permission::getInstance()->getEmployee()->id;
        }
        $nextMonth = Carbon::now()->addMonthNoOverflow()->startOfMonth();
        $fromMonth = null;
        if (isset($data['from_month'])) {
            $fromMonth = Carbon::createFromFormat('m-Y', $data['from_month']);
//            if ($fromMonth->lt($nextMonth)) {
//                return [
//                    'status' => false,
//                    'message' => trans('manage_time::message.Month register must be greater than current month')
//                ];
//            }
        }
        $toMonth = Carbon::createFromFormat('m-Y', $data['to_month']);
        $exists = self::where('employee_id', $empId)
                ->where('from_month', '<=', $toMonth->endOfMonth()->toDateString())
                ->where('to_month', '>=', $fromMonth ? $fromMonth->startOfMonth()->toDateString() : $toMonth->startOfMonth()->toDateString())
                ->where('status', '!=', MTConst::STT_WK_TIME_REJECT)
                ->whereNull('parent_id');
        if (isset($data['id'])) {
            $exists->where('id', '!=', $data['id']);
        }
        $exists = $exists->first();
        if ($exists) {
            return [
                'status' => false,
                'message' => trans('manage_time::message.month_register_already_exists', ['link' => route('manage_time::wktime.register', ['id' => $exists->id])])
            ];
        }
        return [
            'status' => true
        ];
    }

    /*
     * render html status
     */
    public function renderStatusHtml($statuses, $class = 'callout')
    {
        $html = '<div class="'. $class .' text-center white-space-nowrap ' . $class;
        switch ($this->status) {
            case MTConst::STT_WK_TIME_APPROVED:
                $html .= '-success">' . $statuses[$this->status];
                break;
            case MTConst::STT_WK_TIME_NOT_APPROVE:
                $html .= '-warning">' . $statuses[$this->status];
                break;
            case MTConst::STT_WK_TIME_REJECT:
                $html .= '-danger">' . $statuses[$this->status];
                break;
            default:
                return null;
        }
        return $html .= '</div>';
    }

    /*
     * list my register
     */
    public static function listRegister($status = null, $type = 'register')
    {
        $pager = Config::getPagerData();
        $empId = Permission::getInstance()->getEmployee()->id;
        $collection = self::select('wkt.*', 'approver.email as approver_mail', 'emp.email as emp_email')
                ->from(self::getTableName() . ' as wkt')
                ->join(Employee::getTableName() . ' as emp', 'wkt.employee_id', '=', 'emp.id')
                ->leftJoin(Employee::getTableName() . ' as approver', 'wkt.approver_id', '=', 'approver.id')
                ->whereNull('wkt.parent_id')
                ->groupBy('wkt.id');

        switch ($type) {
            case 'register':
                $collection->where('wkt.employee_id', $empId);
                break;
            case 'related':
                $collection->where('wkt.related_ids', 'like', '%"' .$empId. '"%');
                break;
            case 'approve':
                $collection->where('wkt.approver_id', $empId);
                break;
            case 'manage':
                $route = self::ROUTE_MANAGE;
                $collection->leftJoin(TeamMember::getTableName() . ' as tmb', 'wkt.employee_id', '=', 'tmb.employee_id')
                    ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                    ->addSelect(
                        DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") AS team_names')
                    );
                //permission
                if (Permission::getInstance()->isScopeCompany(null, $route)) {
                    //get all
                } elseif (Permission::getInstance()->isScopeTeam(null, $route)) {
                    $teamIds = TeamMember::where('employee_id', $empId)->lists('team_id')->toArray();
                    $teamIds = Team::teamChildIds($teamIds);
                    $collection->where(function ($query) use ($teamIds, $empId) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere('wkt.approver_id', $empId)
                                ->orWhere('wkt.employee_id', $empId)
                                ->orWhere('wkt.related_ids', 'like', '%"'. $empId .'"%');
                    });
                } elseif (Permission::getInstance()->isScopeSelf(null, $route)) {
                    $collection->where(function ($query) use ($empId) {
                        $query->orWhere('wkt.approver_id', $empId)
                                ->orWhere('wkt.employee_id', $empId)
                                ->orWhere('wkt.related_ids', 'like', '%"'. $empId .'"%');
                    });
                } else {
                    CoreView::viewErrorPermission();
                }
                break;
            default:
                break;
        }

        if ($status) {
            $collection->where('status', $status);
        }
        //filter excerpt data
        if ($filterFromMonth = Form::getFilterData('excerpt', 'from_month')) {
            $collection->where(DB::raw('DATE_FORMAT(from_month, "%Y-%m")'), '>=', Carbon::createFromFormat('m-Y', $filterFromMonth)->format('Y-m'));
        }
        if ($filterToMonth = Form::getFilterData('excerpt', 'to_month')) {
            $collection->where(DB::raw('DATE_FORMAT(to_month, "%Y-%m")'), '<=', Carbon::createFromFormat('m-Y', $filterToMonth)->format('Y-m'));
        }
        if ($filterTeam = Form::getFilterData('excerpt', 'team')) {
            $collection->join(TeamMember::getTableName() . ' as ft_tmb', 'wkt.employee_id', '=', 'ft_tmb.employee_id')
                    ->where('ft_tmb.team_id', $filterTeam);
        }
        //filter data
        self::filterGrid($collection);
        //sort order
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('wkt.created_at', 'desc');
        }
        //pager
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * my register statistic
     */
    public static function myStatistic()
    {
        $listStatues = MTConst::listWTStatusesWithIcon();
        $empId = Permission::getInstance()->getEmployee()->id;

        $collect = self::from(self::getTableName() . ' as wkt')
                ->whereNull('parent_id');

        foreach (array_keys($listStatues) as $status) {
            $sqlStatus = '';
            $asStatus = '';
            if ($status) {
                $sqlStatus = ' AND wkt.status = ' . $status;
                $asStatus = '_' . $status;
            }
            //register
            $collect->addSelect(DB::raw('SUM(CASE WHEN wkt.employee_id = '. $empId . $sqlStatus .' THEN 1 ELSE 0 END) AS register' . $asStatus));
            //related
            $collect->addSelect(DB::raw('SUM(CASE WHEN wkt.related_ids like "%\"'. $empId .'\"%"' . $sqlStatus .' THEN 1 ELSE 0 END) AS related' . $asStatus));
            //approve
            $collect->addSelect(DB::raw('SUM(CASE WHEN wkt.approver_id = '. $empId . $sqlStatus .' THEN 1 ELSE 0 END) AS approve' . $asStatus));
        }

        return $collect->first()->toArray();
    }

    /*
     * get list apporvers of employee
     */
    public static function getApproversOfEmp($employee, $approverId = null)
    {
        $team = $employee->newestTeam(true);
        $collect = Employee::getByTeamTypes(Team::CODE_BOD, [], 'code');
        if ($approverId) {
            $approver = Employee::find($approverId, ['id', 'name', 'email']);
            if ($approver) {
                $collect->prepend($approver);
            }
        }
        if (!$team) {
            return $collect;
        }
        if (in_array($team->role_id, [Team::ROLE_MEMBER, Team::ROLE_SUB_LEADER])) {
            $leader = $team->getLeader();
            if ($leader) {
                $collect->prepend($leader);
            }
        }
        return $collect;
    }

    /*
     * get list employees can approve
     */
    public static function getListApprovers($search = null, $config = [])
    {
        $tblEmp = Employee::getTableName();
        $permissTbl = PermissModel::getTableName();
        $actionTbl = Action::getTableName();
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'getFirst' => false
        ];
        $config = array_merge($arrayDefault, $config);

        $collect = Employee::select($tblEmp . '.id', $tblEmp . '.name', $tblEmp . '.email', 'user.avatar_url')
                ->leftJoin(\Rikkei\Core\Model\User::getTableName() . ' as user', $tblEmp . '.id', '=', 'user.employee_id')
                ->leftJoin(TeamMember::getTableName() . ' as tmb', $tblEmp . '.id', '=', 'tmb.employee_id')
                ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                //team
                ->leftJoin($permissTbl . ' as permiss1', function ($join) {
                    $join->on('team.id', '=', 'permiss1.team_id')
                            ->on('tmb.role_id', '=', 'permiss1.role_id');
                })
                ->leftJoin($actionTbl . ' as action1', 'permiss1.action_id', '=', 'action1.id')
                //team follow
                ->leftJoin($permissTbl . ' as permiss2', function ($join) {
                    $join->on('team.follow_team_id', '=', 'permiss2.team_id')
                            ->on('tmb.role_id', '=', 'permiss2.role_id');
                })
                ->leftJoin($actionTbl . ' as action2', 'permiss2.action_id', '=', 'action2.id')
                //employee roles
                ->leftJoin(\Rikkei\Team\Model\EmployeeRole::getTableName() . ' as emp_role', $tblEmp . '.id', '=', 'emp_role.employee_id')
                ->leftJoin($permissTbl . ' as permiss3', 'emp_role.role_id', '=', 'permiss3.role_id')
                ->leftJoin($actionTbl . ' as action3', 'permiss3.action_id', '=', 'action3.id')
                ->where(function ($query) {
                    $query->where(function ($query1) {
                        $query1->where('action1.name', self::ACTION_APPROVE)
                                ->where('permiss1.scope', '!=', PermissModel::SCOPE_NONE);
                    })
                    ->orWhere(function ($query2) {
                        $query2->where('action2.name', self::ACTION_APPROVE)
                                ->where('permiss2.scope', '!=', PermissModel::SCOPE_NONE);
                    })
                    ->orWhere(function ($query3) {
                        $query3->where('action3.name', self::ACTION_APPROVE)
                                ->where('permiss3.scope', '!=', PermissModel::SCOPE_NONE);
                    });
                })
                ->where(function ($query) use ($tblEmp) {
                    $query->whereNull($tblEmp.'.leave_date')
                            ->orWhereRaw($tblEmp . '.leave_date >= CURDATE()');
                })
                ->groupBy($tblEmp . '.id');
        if ($search) {
            $search = trim($search);
            $collect->where(function ($query) use ($tblEmp, $search) {
                $query->where($tblEmp . '.name', 'like', '%' . $search . '%')
                        ->orWhere($tblEmp . '.email', 'like', '%' . $search . '%');
            });
        }
        if ($config['getFirst']) {
            return $collect->first();
        }
        self::pagerCollection($collect, $config['limit'], $config['page']);
        $result['total_count'] = $collect->total();
        $result['items'] = [];
        if ($collect->isEmpty()) {
            return $result;
        }
        foreach ($collect as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => CoreView::getNickName($item->email),
                'avatar' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /*
     * list my working times
     */
    public static function listMyTimes()
    {
        $empId = auth()->id();
        $pager = Config::getPagerData();
        $collection = self::select('id', 'from_month', 'to_month', 'start_time1', 'start_time2', 'end_time1', 'end_time2')
                ->where('employee_id', $empId)
                ->whereNull('parent_id')
                ->where('status', MTConst::STT_WK_TIME_APPROVED);
        if ($filterMonth = Form::getFilterData('excerpt', 'month')) {
            $filterMonth = Carbon::createFromFormat('d-m-Y', '01-' . $filterMonth);
            $collection->where('from_month', '<=', $filterMonth->endOfMonth()->toDateString())
                    ->where('to_month', '>=', $filterMonth->startOfMonth()->toDateString());
        }
        self::filterGrid($collection);
        //sort order
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('from_month', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * list employee logs time
     */
    public static function listLogTimes($month = null)
    {
        $pager = Config::getPagerData();
        $tblEmp = Employee::getTableName();
        $tblTmb = TeamMember::getTableName();

        $collection = Employee::select(
            $tblEmp . '.id',
            $tblEmp . '.employee_code',
            $tblEmp . '.name',
            $tblEmp . '.email',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
            'wkt.from_month',
            'wkt.to_month',
            'wkt.start_time1',
            'wkt.end_time1',
            'wkt.start_time2',
            'wkt.end_time2'
        )
        ->leftJoin($tblTmb . ' as tmb', $tblEmp . '.id', '=', 'tmb.employee_id')
        ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
        ->leftJoin(self::getTableName() . ' as wkt', function ($join) use ($tblEmp) {
            $join->on($tblEmp . '.id', '=', 'wkt.employee_id')
                    ->whereNull('wkt.parent_id')
                    ->where('wkt.status', '=', MTConst::STT_WK_TIME_APPROVED);
        });
        //check permission
        $scope = Permission::getInstance();
        $currUserId = $scope->getEmployee()->id;
        if ($scope->isScopeCompany()) {
            //get all
        } elseif ($scope->isScopeTeam()) {
            $teamIds = TeamMember::where('employee_id', $currUserId)
                    ->lists('team_id')
                    ->toArray();
            $teamIds = Team::teamChildIds($teamIds);
            $collection->where(function ($query) use ($teamIds, $currUserId) {
                $query->whereIn('tmb.team_id', $teamIds)
                        ->orWhere('wkt.employee_id', $currUserId)
                        ->orWhere('wkt.created_by', $currUserId)
                        ->orWhere('wkt.approver_id', $currUserId)
                        ->orWhere('wkt.related_ids', 'like', '%"'. $currUserId .'"%');
            });
        } elseif ($scope->isScopeSelf()) {
            $collection->where(function ($query) use ($currUserId) {
                $query->orWhere('wkt.employee_id', $currUserId)
                        ->orWhere('wkt.created_by', $currUserId)
                        ->orWhere('wkt.approver_id', $currUserId)
                        ->orWhere('wkt.related_ids', 'like', '%"'. $currUserId .'"%');
            });
        } else {
            CoreView::viewErrorPermission();
        }

        if ($month) {
            $month = Carbon::createFromFormat('m-Y', $month);
            $collection->where('wkt.from_month', '<=', $month->endOfMonth()->toDateString())
                ->where('wkt.to_month', '>=', $month->startOfMonth()->toDateString());
        }
        $employeeId = Form::getFilterData('excerpt', 'employee_id');
        if ($employeeId) {
            $collection->where($tblEmp . '.id', $employeeId);
        }

        $showMonth = $employeeId && !$month;
        if ($showMonth) {
            $collection->groupBy($tblEmp . '.id', 'wkt.id');
        } else {
            $collection->groupBy($tblEmp . '.id');
        }

        $collection->where(function ($query) use ($tblEmp, $month) {
            $month = $month ? $month : Carbon::now();
            $query->whereNull($tblEmp.'.leave_date')
                    ->orWhere($tblEmp.'.leave_date', '>=', $month->startOfMonth()->toDateString());
        });
        //filter data
        if ($filterTeam = Form::getFilterData('excerpt', 'team_id')) {
            $collection->join($tblTmb . ' as ft_tmb', $tblEmp . '.id', '=', 'ft_tmb.employee_id')
                    ->where('ft_tmb.team_id', $filterTeam);
        }
        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            if ($showMonth) {
                $collection->orderBy('wkt.from_month', 'desc');
            } else {
                $collection->orderBy($tblEmp . '.email', 'asc');
            }
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public function getWorkingTimeInfo($empId, $date)
    {
        return self::whereDate('from_month', '<=', $date)
            ->whereDate('to_month', '>=', $date)
            ->where('employee_id', $empId)
            ->where('status', MTConst::STT_WK_TIME_APPROVED)
            ->first();
    }

    /**
     * Lấy danh sách đơn đăng ký thay đổi thời gian làm việc của nhân viên đã được approved
     *
     * @param $empId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWorkingTimeList($empId)
    {
        return self::query()
            ->where('employee_id', $empId)
            ->where('status', MTConst::STT_WK_TIME_APPROVED)
            ->get();
    }
}
