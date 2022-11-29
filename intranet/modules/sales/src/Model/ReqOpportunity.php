<?php

namespace Rikkei\Sales\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Sales\View\OpporView;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Country;
use Carbon\Carbon;

class ReqOpportunity extends CoreModel
{
    protected $table = 'request_opportunities';
    protected $fillable = [
        'name',
        'code',
        'priority',
        'status',
        'detail',
        'potential',
        'number_member',
        'number_recieved',
        'lang',
        'duedate',
        'duration',
        'country_id',
        'location',
        'customer_name',
        'curator',
        'curator_email',
        'sale_id',
        'note',
        'created_by'
    ];

    public function members()
    {
        return $this->hasMany('\Rikkei\Sales\Model\ReqOpporMember', 'request_oppor_id');
    }

    public function membersWithProgs()
    {
        return $this->members()->with('programs')->get();
    }

    public function customer()
    {
        return $this->belongsTo('\Rikkei\Sales\Model\Customer', 'customer_id');
    }

    public function creator()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'created_by');
    }

    public function programs()
    {
        return $this->belongsToMany('\Rikkei\Resource\Model\Programs', 'request_oppor_programs', 'req_oppor_id', 'prog_id');
    }

    public function sale()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'sale_id');
    }

    /**
     * get language label
     * @param type $listLabels
     * @return type
     */
    public function getLangLabel($listLabels = null)
    {
        if (!$listLabels) {
            $listLabels = OpporView::listLanguages();
        }
        if (isset($listLabels[$this->lang])) {
            return $listLabels[$this->lang];
        }
        return $this->lang;
    }

    /**
     * get location label
     * @param type $listLocations
     * @return type
     */
    public function getLocationLabel($listLocations = null)
    {
        if (!$listLocations) {
            $listLocations = OpporView::listLocations();
        }
        if (isset($listLocations[$this->location])) {
            return $listLocations[$this->location];
        }
        return $this->location;
    }

    public static function makeCode()
    {
        return 'OP' . sprintf('%07d', self::max('id') + 1);
    }

    public static function checkExists($field, $value, $id = null)
    {
        $result = self::where($field, $value);
        if ($id) {
            $result->where('id', '!=', $id);
        }
        if ($result->first()) {
            return true;
        }
        return false;
    }

    /**
     * get list data
     * @return type
     */
    public static function getGridData($aryIds = null, $isExport = false)
    {
        $pager = Config::getPagerData();
        $collection = self::select(
            'req_op.id',
            'req_op.name',
            'req_op.duedate',
            'sale.email as sale_name',
            'req_op.code',
            'req_op.priority',
            'req_op.status',
            'req_op.number_member',
            'req_op.number_recieved',
            DB::raw('GROUP_CONCAT(DISTINCT(prog.name) SEPARATOR ", ") as prog_names'),
            'req_op.lang',
            'req_op.location',
            'req_op.customer_name as cust_name',
            'creator.email as creator_email',
            'req_op.created_at',
            'req_op.duration',
            'req_op.customer_name',
            'req_op.curator',
            'req_op.curator_email',
            'member.role',
            'country.name as country_name',
            'req_op.created_by'
        )
            ->from(self::getTableName() . ' as req_op')
            ->leftJoin('request_oppor_programs as req_prog', 'req_op.id', '=', 'req_prog.req_oppor_id')
            ->leftJoin(Programs::getTableName() . ' as prog', 'req_prog.prog_id', '=', 'prog.id')
            ->leftJoin(Employee::getTableName() . ' as creator', 'req_op.created_by', '=', 'creator.id')
            ->leftJoin(Employee::getTableName() . ' as sale', 'req_op.sale_id', '=', 'sale.id')
            ->leftJoin(ReqOpporMember::getTableName() . ' as member', 'req_op.id', '=', 'member.request_oppor_id')
            ->leftJoin(Country::getTableName() . ' as country', 'req_op.country_id', '=', 'country.id')
            ->groupBy('req_op.id');

        //permission
        if (Permission::getInstance()->isScopeCompany()) {
            //get all
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $currentUser = Permission::getInstance()->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currentUser->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->join(TeamMember::getTableName() . ' as tmb', 'req_op.created_by', '=', 'tmb.employee_id')
                    ->whereIn('tmb.team_id', $teamIds);
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $currentUser = Permission::getInstance()->getEmployee();
            $collection->where(function ($query) use ($currentUser) {
                $query->where('req_op.created_by', $currentUser->id)
                        ->orWhere('req_op.sale_id', $currentUser->id);
            });
        } else {
            CoreView::viewErrorPermission();
        }

        if ($filterProgIds = Form::getFilterData('excerpt', 'prog_ids')) {
            $collection->leftJoin('request_oppor_programs as ft_req_prog', 'req_op.id', '=', 'ft_req_prog.req_oppor_id')
                    ->leftJoin(Programs::getTableName() . ' as ft_prog', 'ft_req_prog.prog_id', '=', 'ft_prog.id')
                    ->whereIn('ft_prog.id', $filterProgIds);
        }

        if ($aryIds) {
            $collection->whereIn('req_op.id', $aryIds);
        }

        self::filterGrid($collection, [], null, 'LIKE');
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('req_op.created_at', 'desc');
        }
        if ($isExport) {
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * insert or update item
     * @param type $data
     * @return type
     */
    public static function insertOrUpdate($data)
    {
        $dataMembers = isset($data['members']) ? $data['members'] : [];

        $data['lang'] = isset($data['lang']) ? $data['lang'] : '';
        //unset($data['members']);
        $progIds = isset($data['prog_ids']) ? $data['prog_ids'] : [];
        $isChangeData = false;
        //update or insert
        if (isset($data['id'])) {
            $item = self::findOrFail($data['id']);
            $isChangeData = self::isChangeData($item, $data);
            $item->update($data);
        } else {
            $data['created_by'] = auth()->id();
            $item = self::create($data);
            $isChangeData = true;
        }
        //members
        if ($dataMembers) {
            ReqOpporMember::updateByReqId($item->id, $dataMembers);
        }
        if ($progIds) {
            $item->programs()->sync($progIds);
        }
        //after saved
        if ($isChangeData) {
            self::afterSaved($item, $data['id'], isset($data['send_mail']) && $data['send_mail']);
        }
        return $item;
    }

    /*
     * actions after saved
     */
    public static function afterSaved($item, $isUpdate, $isSendMail = false)
    {
        if (!$isSendMail) {
            return;
        }
        $allLeader = TeamMember::getAllLeaders(false, true);
        if ($allLeader->isEmpty()) {
            return;
        }
        $leaderEmails = $allLeader->lists('email')->toArray();
        $firstLeadEmail = $leaderEmails[0];
        $opporLink = route('sales::req.apply.oppor.view', ['id' => $item->id]);
        $textAction = $isUpdate ? trans('sales::view.has_updated') : trans('sales::view.has_created');
        $subject = trans('sales::view.email_subject_submit_opportunity', ['name' => $item->name]) . ' ' . $textAction . '. '
                . trans('sales::view.Please view and apply members');
        $sale = $item->sale;
        $dataMail = [
            'isUpdate' => $isUpdate,
            'opporName' => $item->name,
            'detailLink' => $opporLink,
            'content' => $subject,
            'deadline' => $item->duedate,
            'saleName' => $sale ? $sale->name : null
        ];
        $emailSubmit = new EmailQueue();
        $emailSubmit->setTemplate('sales::req-oppor.mails.submited', $dataMail)
            ->setSubject($subject)
            ->setTo($firstLeadEmail);
        $leaderEmails[] = 'rtc@rikkeisoft.com';
        foreach ($leaderEmails as $bccEmail) {
            $emailSubmit->addBcc($bccEmail);
        }
        $emailSubmit->save();
        //notify
        \RkNotify::put(
            $allLeader->lists('id')->toArray(),
            $subject,
            $opporLink, [
                'category_id' => RkNotify::CATEGORY_PROJECT,
                'content_detail' => RkNotify::renderSections('sales::req-oppor.mails.submited', $dataMail)
            ]
        );
    }

    /*
     * check is change data
     */
    public static function isChangeData($oldItem, $newData)
    {
        $isChange = false;
        $updateData = array_only($newData, $oldItem->getFillable());
        foreach ($updateData as $field => $value) {
            $oldItem->{$field} = $value;
        }
        if ($oldItem->isDirty()) {
            $isChange = true;
        }
        if ($isChange) {
            return true;
        }

        $oldMembers = $oldItem->membersWithProgs();
        $newMembers = isset($newData['members']) ? $newData['members'] : [];
        // one empty other not
        if ($oldMembers->isEmpty() && $newMembers || !$oldMembers->isEmpty() && !$newMembers) {
            return true;
        }

        if (!$oldMembers->isEmpty() && $newMembers) { // both has data
            if ($oldMembers->count() != count($newMembers)) { // diff count mmember
                return true;
            }
            // same size of member
            $aryNewMembers = [];
            foreach ($newMembers as $newMember) {
                //has new member
                if (!isset($newMember['id']) || !$newMember['id']) {
                    return true;
                }
                $aryNewMembers[$newMember['id']] = $newMember;
            }
            foreach ($oldMembers as $member) {
                //has diff member
                if (!isset($aryNewMembers[$member->id])) {
                    return true;
                }

                $member->prog_ids = $member->programs->lists('id')->toArray();
                $newMember = $aryNewMembers[$member->id];
                $fieldsCompare = ['role', 'prog_ids', 'member_exp', 'english_level', 'japanese_level'];
                foreach ($fieldsCompare as $field) {
                    $value = isset($newMember[$field]) ? $newMember[$field] : null;
                    if (!$member->{$field} && !$value) {
                        continue;
                    }
                    //is array value
                    if (is_array($value)) {
                        if (array_diff($member->{$field}, $value) || array_diff($value, $member->{$field})) {
                            return true;
                        }
                    } else {
                        if ($member->{$field} != $value) {
                            return true;
                        }
                    }
                }
            }
        }

        return $isChange;
    }

    /**
     * get list member to export
     * @param type $reqId
     * @return type
     */
    public static function getMembersExport($reqId)
    {
        return ReqOpporMember::select(
            'member.number',
            DB::raw('GROUP_CONCAT(DISTINCT(prog.name) SEPARATOR ", ") as prog_names'),
            'member.member_exp',
            'member.role',
            'member.english_level',
            'member.japanese_level'
        )
                ->from(ReqOpporMember::getTableName() . ' as member')
                ->leftJoin('request_oppor_member_program as mb_prog', 'member.id', '=', 'mb_prog.req_member_id')
                ->leftJoin(Programs::getTableName() . ' as prog', 'mb_prog.prog_id', '=', 'prog.id')
                ->where('member.request_oppor_id', $reqId)
                ->groupBy('member.id')
                ->get();
    }

    /*
     * cronjob send mail alert deadline or not change status
     */
    public static function cronSendMailAlert()
    {
        $empTbl = Employee::getTableName();
        DB::beginTransaction();
        try {
            //collect get items not change satatus after 2 days
            $dateTwoDays = Carbon::now()->subDays(2)->toDateString();
            $collection = self::select(
                'opp.id',
                'opp.sale_id',
                'opp.name',
                'opp.created_by',
                'emp.name as sale_name',
                'emp.email as sale_email',
                'creator.email as creator_email'
            )
                ->from(self::getTableName() . ' as opp')
                ->join($empTbl . ' as emp', 'emp.id', '=', 'opp.sale_id')
                ->leftJoin($empTbl . ' as creator', 'creator.id', '=', 'opp.created_by')
                ->where(DB::raw('DATE(opp.created_at)'), '=', $dateTwoDays)
                ->where('opp.status', OpporView::STT_OPEN)
                ->whereNotNull('opp.sale_id')
                ->groupBy('opp.id')
                ->get();

            if (!$collection->isEmpty()) {
                foreach ($collection as $oppor) {
                    $emailData = [
                        'dearName' => $oppor->sale_name,
                        'oppors' => [
                            [
                                'id' => $oppor->id,
                                'name' => $oppor->name
                            ]
                        ]
                    ];
                    $subject = trans('sales::view.mail_subject_alert_status_open', ['name' => $oppor->name]);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($oppor->sale_email)
                            //not change status
                            ->setTemplate('sales::req-oppor.mails.alert-status', $emailData)
                            ->setSubject($subject)
                            ->setNotify(
                                $oppor->sale_id,
                                $subject,
                                route('sales::req.apply.oppor.view', $oppor->id),
                                ['actor_id' => null, 'category_id' => RkNotify::CATEGORY_PROJECT]
                            );
                    if ($oppor->sale_id != $oppor->created_by) {
                        $emailQueue->addCc($oppor->creator_email)
                                ->addCcNotify($oppor->created_by);
                    }
                    $emailQueue->save();
                }
            }

            //collect get items not close or cancel before 3 days from deadline
            $dateBeforeThreeDays = Carbon::now()->addDays(3)->toDateString();
            $collectDeadline = self::select(
                'opp.id',
                'opp.sale_id',
                'opp.duedate',
                'opp.name',
                'opp.created_by',
                'emp.name as sale_name',
                'emp.email as sale_email',
                'creator.email as creator_email'
            )
                ->from(self::getTableName() . ' as opp')
                ->join($empTbl . ' as emp', 'emp.id', '=', 'opp.sale_id')
                ->leftJoin($empTbl . ' as creator', 'creator.id', '=', 'opp.created_by')
                ->where('opp.duedate', '=', $dateBeforeThreeDays)
                ->whereNotIn('opp.status', [OpporView::STT_CLOSED, OpporView::STT_CANCEL])
                ->whereNotNull('opp.sale_id')
                ->groupBy('opp.id')
                ->orderBy('opp.duedate', 'desc')
                ->get();;

            //send to sales
            if (!$collectDeadline->isEmpty()) {
                foreach ($collectDeadline as $oppor) {
                    $emailData = [
                        'dearName' => $oppor->sale_name,
                        'oppors' => [
                            $oppor->toArray()
                        ],
                        'isSale' => 1
                    ];
                    $subject = trans('sales::view.mail_subject_alert_before_deadline', ['name' => $oppor->name]);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($oppor->sale_email)
                            //before deadline
                            ->setTemplate('sales::req-oppor.mails.alert-before-deadline', $emailData)
                            ->setSubject($subject)
                            ->setNotify(
                                $oppor->sale_id,
                                $subject,
                                route('sales::req.apply.oppor.view', $oppor->id),
                                ['actor_id' => null, 'category_id' => RkNotify::CATEGORY_PROJECT]
                            );
                    if ($oppor->sale_id != $oppor->created_by) {
                        $emailQueue->addCc($oppor->creator_email)
                                ->addCcNotify($oppor->created_by);
                    }
                    $emailQueue->save();
                }
            }

            //send to all leader PTPM
            if (!$collectDeadline->isEmpty()) {
                $allLeader = TeamMember::getAllLeaders(false, true);
                if (!$allLeader->isEmpty()) {
                    $leaderEmails = $allLeader->lists('email')->toArray();
                    $firstLeadEmail = $leaderEmails[0];
                    $dataMail = [
                        'oppors' => $collectDeadline->toArray(),
                    ];
                    $subject = trans('sales::view.mail_subject_alert_before_deadline_leader');
                    $emailLeader = new EmailQueue();
                    //before deadline
                    $emailLeader->setTemplate('sales::req-oppor.mails.alert-before-deadline-leader', $dataMail)
                            ->setSubject($subject)
                            ->setTo($firstLeadEmail);
                    foreach ($leaderEmails as $bccEmail) {
                        $emailLeader->addBcc($bccEmail);
                    }
                    $emailLeader->save();
                    //notify
                    \RkNotify::put(
                        $allLeader->lists('id')->toArray(),
                        $subject . ' (' . trans('sales::view.detail in mail') . ')',
                        $collectDeadline->count() == 1 ? route('sales::req.apply.oppor.view', $collectDeadline->first()->id) : null, [
                            'actor_id' => null,
                            'category_id' => RkNotify::CATEGORY_PROJECT,
                            'content_detail' => RkNotify::renderSections('sales::req-oppor.mails.alert-before-deadline-leader', $dataMail)
                        ]);
                }
            }

            //collect get items deadline send to sales
            $dateNow = Carbon::now()->toDateString();
            $collectEqualDeadline = self::select(
                'opp.id',
                'opp.sale_id',
                'opp.name',
                'opp.created_by',
                'emp.name as sale_name',
                'emp.email as sale_email',
                'creator.email as creator_email'
            )
                ->from(self::getTableName() . ' as opp')
                ->join($empTbl . ' as emp', 'emp.id', '=', 'opp.sale_id')
                ->leftJoin($empTbl . ' as creator', 'creator.id', '=', 'opp.created_by')
                ->where('opp.duedate', $dateNow)
                ->whereNotIn('opp.status', [OpporView::STT_CLOSED, OpporView::STT_CANCEL])
                ->whereNotNull('opp.sale_id')
                ->groupBy('opp.id')
                ->get();

            if (!$collectEqualDeadline->isEmpty()) {
                foreach ($collectEqualDeadline as $oppor) {
                    $emailData = [
                        'dearName' => $oppor->sale_name,
                        'oppors' => [
                            $oppor->toArray()
                        ]
                    ];
                    $subject = trans('sales::view.mail_subject_alert_deadline', ['name' => $oppor->name]);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($oppor->sale_email)
                            //deadline
                            ->setTemplate('sales::req-oppor.mails.alert-deadline', $emailData)
                            ->setSubject($subject)
                            ->setNotify(
                                $oppor->sale_id,
                                $subject . ' (' . trans('sales::view.detail in mail') . ')',
                                route('sales::req.apply.oppor.view', $oppor->id),
                                ['actor_id' => null, 'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                            );
                    if ($oppor->sale_id != $oppor->created_by) {
                        $emailQueue->addCc($oppor->creator_email)
                                ->addCcNotify($oppor->created_by);
                    }
                    $emailQueue->save();
                }
            }

            //auto close expire deadline
            self::where('duedate', '<', Carbon::now()->toDateString())
                ->whereNotIn('status', [OpporView::STT_CLOSED, OpporView::STT_CANCEL])
                ->update(['status' => OpporView::STT_CLOSED]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

    /*
     * permiss edit one item
     */
    public static function permissEdit($oppor)
    {
        $route = 'sales::req.oppor.edit';
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            return true;
        }
        $currUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeTeam(null, $route)) {
            $hasItem = self::from(self::getTableName() . ' as opp')
                    ->join(TeamMember::getTableName() . ' as tmb', 'tmb.employee_id', '=', 'opp.created_by')
                    ->where('opp.id', $oppor->id)
                    ->whereIn('tmb.team_id', function ($query) use ($currUser) {
                        $query->select('team_id')
                                ->from(TeamMember::getTableName())
                                ->where('employee_id', $currUser->id);
                    })
                    ->first();
            if ($hasItem) {
                return true;
            }
        }
        return in_array($currUser->id, [$oppor->created_by]);
    }

    /*
     * check list of oppors ids permission, $createdBy use in edit page
     */
    public static function permissEditOppors($opporIds = [], $createdBy = null)
    {
        $tblRq = self::getTableName();
        $routeEdit = 'sales::req.oppor.edit';
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $routeEdit)) {
            if ($createdBy) {
                return true;
            }
            return 'company';
        }
        if ($scope->isScopeTeam(null, $routeEdit)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $rqIds = self::join(TeamMember::getTableName() . ' as tmb', $tblRq . '.created_by', '=', 'tmb.employee_id')
                    ->where(function ($query) use ($teamIds) {
                        $query->whereIn('tmb.team_id', $teamIds);
                    })
                    ->whereIn($tblRq . '.id', $opporIds)
                    ->lists($tblRq . '.id')
                    ->toArray();
            if ($createdBy) {
                if ($rqIds) {
                    return true;
                }
                return false;
            }
            return $rqIds;
        }
        if ($scope->isScopeSelf(null, $routeEdit)) {
            if ($createdBy) {
                return $createdBy == $scope->getEmployee()->id;
            }
            return 'self';
        }
        if ($createdBy && $createdBy == $scope->getEmployee()->id) {
            return true;
        }
        return [];
    }

    /*
     * check permission foreach item in list
     */
    public static function checkPermissInList($requestId, $listPermiss = [], $createdBy = null)
    {
        if ($listPermiss == 'company') {
            return true;
        }
        if (is_array($listPermiss) && in_array($requestId, $listPermiss)) {
            return true;
        }
        if ($listPermiss == 'self') {
            return $createdBy == auth()->id();
        }
        return false;
    }

}

