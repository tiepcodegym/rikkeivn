<?php

namespace Rikkei\SubscriberNotify\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\LeaveDayGroupEmail;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\LeaveDayRelater;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;

class LeaveDayController extends Controller
{
    const TYPE_REGISTER = 1; //insert or update
    const TYPE_APPROVED = 2;
    const TYPE_REJECTED = 3;

    /**
     * @param Request $request
     * @param $employee_id
     * @param $type
     * @return bool
     */
    public function subscriber(Request $request, $employee_id, $type)
    {
        DB::beginTransaction();
        try {
            $employeeInfo = Employee::getEmpById($employee_id);
            if (!$employeeInfo) {
                throw new Exception('User send not found');
            }
            $leaveDayId = $request->get('id');
            $registerRecord = LeaveDayRegister::getInformationRegister($leaveDayId);
            if (!$registerRecord || !$registerRecord->id) {
                throw new Exception('Leaveday not found');
            }
            switch (intval($type)) {
                case self::TYPE_REGISTER:
                    if ($registerRecord->status != LeaveDayRegister::STATUS_UNAPPROVE) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->creator_id != ($employeeInfo->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyRegister($registerRecord, $employeeInfo);
                    break;
                case self::TYPE_APPROVED:
                    if ($registerRecord->status != LeaveDayRegister::STATUS_APPROVED) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->approver_id != ($employeeInfo->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyApproved($registerRecord, $employeeInfo);
                    break;
                case self::TYPE_REJECTED:
                    if ($registerRecord->status != LeaveDayRegister::STATUS_DISAPPROVE) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->approver_id != ($employeeInfo->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyRejected($registerRecord, $employeeInfo);
                    break;
                default:
                    throw new Exception('Param is invalid');
                    break;
            }
            DB::commit();
            return response()->json(['success' => 1, 'message' => 'success'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            return response()->json(['success' => 0, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * @param LeaveDayRegister $registerRecord
     * @param Employee $employee
     * @throws Exception
     */
    protected function pushNotifyRegister(LeaveDayRegister $registerRecord, Employee $employee)
    {
        $data = [];
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->approver_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Unapprove');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['approver_name'] = $registerRecord->approver_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['number_days_off'] = $registerRecord->number_days_off;
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->note, 50));
        $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecord->id]);
        $data['to_id'] = $registerRecord->approver_id;
        $data['noti_content'] = $data['mail_title'];

        $template = 'manage_time::template.leave.mail_register.mail_register_to_approver';
        $data['actor_id'] = $employee->id;
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

        if (!$r) {
            throw new Exception('Insert notify failed');
        }

        if ($registerRecord->substitute_id) {
            $data['mail_to'] = $registerRecord->substitute_email;
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            $data['substitute_name'] = $registerRecord->substitute_name;
            $data['to_id'] = $registerRecord->substitute_id;
            $data['noti_content'] = $data['mail_title'];
            $template = 'manage_time::template.leave.mail_register.mail_register_to_substitute';
            $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
            if (!$r) {
                throw new Exception('Insert notify failed');
            }
        }
        $relatedPersonsId = LeaveDayRelater::where('register_id', $registerRecord->id)->pluck('relater_id')->toArray();
        if (count($relatedPersonsId)) {
            $relatePersons = Employee::getEmpByIds($relatedPersonsId);
            foreach ($relatePersons as $person) {
                $registerRelaters[] = array('register_id' => $registerRecord->id, 'relater_id' => $person->id);

                //Send mail to relaters
                $data['mail_to'] = $person->email;
                $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
                $data['substitute_name'] = $registerRecord->substitute_name;
                $data['to_id'] = $person->id;
                $data['noti_content'] = $data['mail_title'];
                $data['related_person_name'] = $person->name;
                $template = 'manage_time::template.leave.mail_register.mail_register_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
        }

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerRecord->id);

        $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
        if (!empty($groupEmail)) {
            $emailGroup = '';
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            $data['substitute_name'] = $registerRecord->substitute_name;
            $data['noti_content'] = $data['mail_title'];
            $template = 'manage_time::template.leave.mail_register.mail_register_to_group_email';
            foreach ($groupEmail as $value) {
                if (in_array($value, $groupEmailRegister)) {
                    $emailGroup = $emailGroup.$value.';';
                    $data['mail_to'] = $value;
                    ManageTimeCommon::pushEmailToQueue($data, $template);
                }
            }
            $leaveDayGroupEmail = [
                'register_id' => $registerRecord->id,
                'group_email' => rtrim($emailGroup, ";")
            ];
           LeaveDayGroupEmail::insert($leaveDayGroupEmail);
        }
    }

    /**
     * @param LeaveDayRegister $registerRecord
     * @param Employee $employee
     * @throws Exception
     */
    protected function pushNotifyApproved(LeaveDayRegister $registerRecord, Employee $employee)
    {
        $data = [];
        // Push email and notification
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->creator_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Approved][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Approved');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['number_days_off'] = $registerRecord->number_days_off;
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->note, 50));
        $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecord->register_id]);
        $data['approver_name'] = $registerRecord->approver_name;
        $data['approver_position'] = '';
        $approver = $registerRecord->getApproverInformation();
        if ($approver) {
            $data['approver_position'] = $approver->approver_position;
        }
        $data['to_id'] = $registerRecord->creator_id;
        $data['noti_content'] = trans('manage_time::view.The register of leave day has been considered:') . ' ' . $data['status'];

        $template = 'manage_time::template.leave.mail_approve.mail_approve_to_registrant';
        $data['actor_id'] = $employee->id;
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
        if (!$r) {
            throw new Exception('Insert notify failed');
        }
        if ($registerRecord->substitute_id) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            $data['mail_to'] = $registerRecord->substitute_email;
            $data['substitute_name'] = $registerRecord->substitute_name;
            $data['to_id'] = $registerRecord->substitute_id;
            $data['noti_content'] = trans(
                'manage_time::view.The register of leave day of :registrant_name, :team_name which you replace job is considered:',
                $data
            ) . ' ' . $data['status'];
            $template = 'manage_time::template.leave.mail_approve.mail_approve_to_substitute';
            $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
            if (!$r) {
                throw new Exception('Insert notify failed');
            }
        }

        $relatedPersons = LeaveDayRelater::getRelatedPersons($registerRecord->id);
        if (count($relatedPersons)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($relatedPersons as $item) {
                $data['mail_to'] = $item->relater_email;
                $data['related_person_name'] = $item->relater_name;
                $template = 'manage_time::template.leave.mail_approve.mail_approve_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('relater_id')->toArray(),
                trans('manage_time::view.The register of leave day of :registrant_name, :team_name related to you is considered:', $data) . ' ' . $data['status'],
                $data['link'],
                $notificationData
            );
        }

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerRecord->id);
        if ($groupEmail && count($groupEmail)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            $template = 'manage_time::template.leave.mail_approve.mail_approve_to_group_email';
            foreach ($groupEmail as $item) {
                $data['mail_to'] = $item;
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
        }
    }

    /**
     * @param LeaveDayRegister $registerRecord
     * @param Employee $employee
     * @throws Exception
     */
    protected function pushNotifyRejected(LeaveDayRegister $registerRecord, Employee $employee)
    {
        $commentInfo = ManageTimeComment::where('register_id', $registerRecord->id)->where('type', ManageTimeConst::TYPE_LEAVE_DAY)->first();
        $reasonDisapprove = $commentInfo ? $commentInfo->comment : '';
        $data = [];
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->creator_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Unapproved][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Unapprove');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['number_days_off'] = $registerRecord->number_days_off;
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
        $data['note'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->note, 50));
        $data['link'] = route('manage_time::profile.leave.detail', ['id' => $registerRecord->register_id]);
        $data['to_id'] = $registerRecord->creator_id;
        $data['noti_content'] = trans('manage_time::view.The register of leave day has been considered:') . ' ' . $data['status'];

        $template = 'manage_time::template.leave.mail_disapprove.mail_disapprove_to_registrant';
        $data['actor_id'] = $employee->id;
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
        if (!$r) {
            throw new Exception('Insert notify failed');
        }

        if ($registerRecord->substitute_id) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            $data['mail_to'] = $registerRecord->substitute_email;
            $data['substitute_name'] = $registerRecord->substitute_name;
            $data['to_id'] = $registerRecord->substitute_id;
            $data['noti_content'] = trans(
                    'manage_time::view.The register of leave day of :registrant_name, :team_name which you replace job is considered:',
                    $data
                ) . ' ' . $data['status'];
            $template = 'manage_time::template.leave.mail_approve.mail_approve_to_substitute';
            $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
            if (!$r) {
                throw new Exception('Insert notify failed');
            }
        }

        $relatedPersons = LeaveDayRelater::getRelatedPersons($registerRecord->id);
        if (count($relatedPersons)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($relatedPersons as $item) {
                $data['mail_to'] = $item->relater_email;
                $data['related_person_name'] = $item->relater_name;
                $template = 'manage_time::template.leave.mail_approve.mail_approve_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('relater_id')->toArray(),
                trans('manage_time::view.The register of leave day of :registrant_name, :team_name related to you is considered:', $data) . ' ' . $data['status'],
                $data['link'],
                $notificationData
            );
        }

        $groupEmail = LeaveDayGroupEmail::getGroupEmail($registerRecord->id);
        if ($groupEmail && count($groupEmail)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Leave day] :name register leave day, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($groupEmail as $item) {
                $data['mail_to'] = $item;
                $data['status'] = Lang::get('manage_time::view.Unapprove');
                $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonDisapprove, 50));
                $template = 'manage_time::template.leave.mail_disapprove.mail_disapprove_to_group_email';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
        }
    }
}
