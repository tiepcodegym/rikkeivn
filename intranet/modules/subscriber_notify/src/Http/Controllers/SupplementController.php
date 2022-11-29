<?php

namespace Rikkei\SubscriberNotify\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\SupplementRelater;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeConst;

class SupplementController extends Controller
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
            $resp = false;
            $employee = Employee::getEmpById($employee_id);
            if (!$employee) {
                throw new Exception('User send not found');
            }
            $id = $request->get('id');
            $registerRecord = SupplementRegister::getInformationRegister($id);
            if (!$registerRecord) {
                throw new Exception('Supplement not found');
            }
            switch (intval($type)) {
                case self::TYPE_REGISTER:
                    if ($registerRecord->status != SupplementRegister::STATUS_UNAPPROVE) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->creator_id != ($employee->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyRegister($registerRecord, $employee);
                    break;
                case self::TYPE_APPROVED:
                    if ($registerRecord->status != SupplementRegister::STATUS_APPROVED) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->approver_id != ($employee->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyApproved($registerRecord, $employee);
                    break;
                case self::TYPE_REJECTED:
                    if ($registerRecord->status != SupplementRegister::STATUS_DISAPPROVE) {
                        throw new Exception('Param is invalid');
                    }
                    if((int)$registerRecord->approver_id != ($employee->id))
                    {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyRejected($registerRecord, $employee);
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
     * @param SupplementRegister $registerRecord
     * @param Employee $employee
     * @throws Exception
     */
    protected function pushNotifyRegister(SupplementRegister $registerRecord, Employee $employee)
    {
        $data = [];
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->approver_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Unapprove');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['approver_name'] = $registerRecord->approver_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]);
        $data['to_id'] = $registerRecord->approver_id;
        $data['noti_content'] = $data['mail_title'];

        $template = 'manage_time::template.supplement.mail_register.mail_register_to_approver';
        $data['actor_id'] = $employee->id;
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $relatedPersons = SupplementRelater::getRelatedPersons($registerRecord->id);
        if (count($relatedPersons)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($relatedPersons as $item) {
                $data['mail_to'] = $item->relater_email;
                $data['related_person_name'] = $item->relater_name;
                $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('relater_id')->toArray(),
                $data['mail_title'],
                $data['link'],
                $notificationData
            );
        }
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

        if (!$r) {
            throw new Exception('Insert notify failed');
        }
    }


    /**
     * @param Request $request
     * @param Employee $employee
     * @return bool
     * @throws Exception
     */
    protected function pushNotifyApproved(SupplementRegister $registerRecord, Employee $employee)
    {
        $data = [];
        $data['actor_id'] = $employee->id;
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->creator_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Approved][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Approved');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]);
        $data['approver_name'] = $registerRecord->approver_name;
        $data['approver_position'] = '';
        $approver = $registerRecord->getApproverInformation();
        if ($approver) {
            $data['approver_position'] = $approver->approver_position;
        }
        $data['to_id'] = $registerRecord->creator_id;
        $data['noti_content'] = trans('manage_time::view.The register of supplement has been considered:') . ' ' . $data['status'];

        $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_registrant';
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
        if (!$r) {
            throw new Exception('Insert notify failed');
        }
        $relatedPersons = SupplementRelater::getRelatedPersons($registerRecord->id);
        if (count($relatedPersons)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($relatedPersons as $item) {
                $data['mail_to'] = $item->relater_email;
                $data['related_person_name'] = $item->relater_name;
                $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('relater_id')->toArray(),
                trans('manage_time::view.The register of supplement of :registrant_name, :team_name related to you is considered:', $data) . ' ' . $data['status'],
                $data['link'],
                $notificationData
            );
        }
    }


    /**
     * @param Request $request
     * @param Employee $employee
     * @return bool
     * @throws Exception
     */
    protected function pushNotifyRejected(SupplementRegister $registerRecord, Employee $employee)
    {
        $commentInfo = ManageTimeComment::where('register_id', $registerRecord->id)->where('type', ManageTimeConst::TYPE_SUPPLEMENT)->first();
        $reasonReject = $commentInfo ? $commentInfo->comment : '';
        $data = [];
        $data['actor_id'] = $employee->id;
        $data['user_mail'] = $employee->email;
        $data['mail_to'] = $registerRecord->creator_email;
        $data['mail_title'] = Lang::get('manage_time::view.[Unapproved][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
        $data['status'] = Lang::get('manage_time::view.Unapprove');
        $data['registrant_name'] = $registerRecord->creator_name;
        $data['team_name'] = $registerRecord->role_name;
        $data['start_date'] = Carbon::parse($registerRecord->date_start)->format('d/m/Y');
        $data['start_time'] = Carbon::parse($registerRecord->date_start)->format('H:i');
        $data['end_date'] = Carbon::parse($registerRecord->date_end)->format('d/m/Y');
        $data['end_time'] = Carbon::parse($registerRecord->date_end)->format('H:i');
        $data['reason'] = View::nl2br(ManageTimeCommon::limitText($registerRecord->reason, 50));
        $data['reason_disapprove'] = View::nl2br(ManageTimeCommon::limitText($reasonReject, 50));
        $data['link'] = route('manage_time::profile.supplement.detail', ['id' => $registerRecord->register_id]);
        $data['to_id'] = $registerRecord->creator_id;
        $data['noti_content'] = trans('manage_time::view.The register of supplement has been considered:') . ' ' . $data['status'];

        $template = 'manage_time::template.supplement.mail_disapprove.mail_disapprove_to_registrant';
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        $r = ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
        if (!$r) {
            throw new Exception('Insert notify failed');
        }
        $relatedPersons = SupplementRelater::getRelatedPersons($registerRecord->id);
        if (count($relatedPersons)) {
            $data['mail_title'] = Lang::get('manage_time::view.[Notification][Supplement] :name register supplement, from date :start_date to date :end_date', ['name' => $registerRecord->creator_name, 'start_date' => Carbon::parse($registerRecord->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecord->date_end)->format('d/m/Y')]);
            foreach ($relatedPersons as $item) {
                $data['mail_to'] = $item->relater_email;
                $data['related_person_name'] = $item->relater_name;
                $template = 'manage_time::template.supplement.mail_approve.mail_approve_to_related_person';
                $r = ManageTimeCommon::pushEmailToQueue($data, $template);
                if (!$r) {
                    throw new Exception('Insert notify failed');
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('relater_id')->toArray(),
                trans('manage_time::view.The register of supplement of :registrant_name, :team_name related to you is considered:', $data) . ' ' . $data['status'],
                $data['link'],
                $notificationData
            );
        }
    }
}
