<?php
namespace Rikkei\Ot\View;

use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\View;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\ManageTime\Model\ManageTimeComment;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Notify\Classes\RkNotify;
class OtEmailManagement
{

    /**
     * push email to queue
     * @param  array $data
     * @param  string $template
     * @return boolean
     */
    public static function pushEmailToQueue($data, $template, $notify = false, $notificationData = [])
    {
        $subject = $data['mail_title'];
        $emailQueue = new EmailQueue();

        $emailQueue->setTo($data['mail_to'])
                   ->setSubject($subject)
                   ->setTemplate($template, $data);
        //set notify
        if ($notify && isset($data['to_id'])) {
            $dataNotify = [];
            if(isset($data['actor_id']))
            {
                $dataNotify['actor_id'] = $data['actor_id'];
            }
            $dataNotify['category_id'] = !empty($notificationData['category_id']) ? $notificationData['category_id'] : RkNotify::CATEGORY_OTHER;
            $emailQueue->setNotify($data['to_id'], $data['noti_content'], $data['link'], $dataNotify);
        }
        try {
            $emailQueue->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * set email for ot registration
     * @param OtRegister $register
     * @param string $template
     * @param string $actor_id
     */
    public static function setEmailRegister($register, $template,$actor_id = null)
    {
        $data = [];
        $regEmp = $register->getCreatorInformation();
        $approver = $register->getApproverInformation();
        $tagEmployees = $register->ot_employees()->get();
        if($actor_id)
        {
            $data['actor_id'] = $actor_id;
        }
        $data['status'] = Lang::get('ot::view.Unapproved Label');
        $data['start_at'] = Carbon::parse($register->start_at)->format('d/m/Y H:i');
        $data['start_date'] = Carbon::parse($register->start_at)->format('d/m/Y');
        $data['end_at'] = Carbon::parse($register->end_at)->format('d/m/Y H:i');
        $data['end_date'] = Carbon::parse($register->end_at)->format('d/m/Y');
        $data['time_break'] = $register->time_break;
        $data['reason'] = View::nl2br(self::limitText($register->reason, 50));
        $data['link'] = route('ot::ot.detail', ['id' => $register->id]);
        $data['team_name'] = '';
        $data['register_name'] = '';
        if ($regEmp) {
            $data['team_name'] = $regEmp->creator_position;
            $data['register_name'] = $regEmp->creator_name;
        }
        if ($approver) {
            $data['mail_to'] = $approver->approver_email;
            $data['mail_title'] = Lang::get('ot::view.[Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            $data['approver_name'] = $approver->approver_name;
            $data['to_id'] = $approver->approver_id;
            $data['noti_content'] = $data['mail_title'];
            $notificationData = [
                'category_id' => RkNotify::CATEGORY_TIMEKEEPING
            ];
            self::pushEmailToQueue($data, $template, true, $notificationData);
        }
        if (count($tagEmployees)) {
            $template = 'ot::template.mail_register.mail_register_to_employee_ot';
            $data['mail_title'] = Lang::get('ot::view.[Notification][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            $dataReciverIds = [];
            foreach ($tagEmployees as $emp) {
                if ($regEmp->creator_id == $emp->employee_id) {
                    $data['is_ot'] = count($tagEmployees) == getOptions::STATUS_INPROGRESS ? true : false;
                    continue;
                }
                $empNameEmail = Employee::getNameEmailById($emp->employee_id);
                if ($empNameEmail) {
                    $data['start_at'] = Carbon::parse($emp->start_at)->format('d/m/Y H:i');
                    $data['start_date'] = Carbon::parse($emp->start_at)->format('d/m/Y');
                    $data['end_at'] = Carbon::parse($emp->end_at)->format('d/m/Y H:i');
                    $data['end_date'] = Carbon::parse($emp->end_at)->format('d/m/Y');
                    $data['mail_to'] = $empNameEmail->email;
                    $data['receiver_name'] = $empNameEmail->name;
                    self::pushEmailToQueue($data, $template);
                }
                $dataReciverIds[] = $emp->employee_id;
            }
            $data['category_id'] = RkNotify::CATEGORY_TIMEKEEPING;
            //set notify
            \RkNotify::put(
                $dataReciverIds,
                $data['mail_title'],
                $data['link'],
                $data
            );
        }
    }

    /*
     * Set mail when approve or disapprove
     */
    public static function setEmailApproverAction($register, $template, $actionType,$actor_id = null)
    {
        $data = [];
        $regEmp = $register->getCreatorInformation();
        $approver = $register->getApproverInformation();
        $tagEmployees = $register->ot_employees()->get();

        if($actor_id)
        {
            $data['actor_id'] = $actor_id;
        }
        $data['status'] = Lang::get('ot::view.Approved Label');
        $data['start_at'] = Carbon::parse($register->start_at)->format('d/m/Y H:i');
        $data['start_date'] = Carbon::parse($register->start_at)->format('d/m/Y');
        $data['end_at'] = Carbon::parse($register->end_at)->format('d/m/Y H:i');
        $data['end_date'] = Carbon::parse($register->end_at)->format('d/m/Y');
        $data['time_break'] = $register->time_break;
        $data['reason'] = View::nl2br(self::limitText($register->reason, 50));
        $data['link'] = route('ot::ot.detail', ['id' => $register->id]);
        $data['team_name'] = '';
        $data['register_name'] = '';
        $data['approver_name'] = '';
        $data['approverr_position'] = '';
        if ($approver) {
            $data['approver_name'] = $approver->approver_name;
            $data['approver_position'] = $approver->approver_position;
        }
        if ($actionType == OtRegister::REJECT) {
            $reason = ManageTimeComment::where('register_id', $register->id)->where('type', ManageTimeConst::TYPE_OT)->first();
            $data['reason_disapprove'] = '';
            if ($reason) {
                $data['reason_disapprove'] = $reason->comment;
            }
            $data['status'] = Lang::get('ot::view.Unapproved Label');
        }
        if ($regEmp) {
            $data['team_name'] = $regEmp->creator_position;
            $data['register_name'] = $regEmp->creator_name;
            $data['mail_to'] = $regEmp->creator_email;
            $data['mail_title'] = Lang::get('ot::view.[Approved][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            if ($actionType == OtRegister::REJECT) {
                $data['mail_title'] = Lang::get('ot::view.[Unapproved][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            }
            $data['to_id'] = $regEmp->creator_id;
            $data['noti_content'] = $data['mail_title'];
            $notificationData = [
                'category_id' => RkNotify::CATEGORY_TIMEKEEPING
            ];
            self::pushEmailToQueue($data, $template, true, $notificationData);
        }

        if (count($tagEmployees)) {
            $template = 'ot::template.mail_approve.mail_approver_to_employee_ot';
            if ($actionType == OtRegister::REJECT) {
                $template = 'ot::template.mail_disapprove.mail_disapprover_to_employee_ot';
            }
            $data['mail_title'] = Lang::get('ot::view.[Notification][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            $dataReciverIds = [];
            foreach ($tagEmployees as $emp) {
                if ($regEmp->creator_id == $emp->employee_id) {
                    $data['is_ot'] = count($tagEmployees) == getOptions::STATUS_INPROGRESS ? true : false;
                    continue;
                }
                $empNameEmail = Employee::getNameEmailById($emp->employee_id);
                if ($empNameEmail) {
                    $data['start_at'] = Carbon::parse($emp->start_at)->format('d/m/Y H:i');
                    $data['start_date'] = Carbon::parse($emp->start_at)->format('d/m/Y');
                    $data['end_at'] = Carbon::parse($emp->end_at)->format('d/m/Y H:i');
                    $data['end_date'] = Carbon::parse($emp->end_at)->format('d/m/Y');
                    $data['mail_to'] = $empNameEmail->email;
                    $data['receiver_name'] = $empNameEmail->name;
                    self::pushEmailToQueue($data, $template);
                }
                $dataReciverIds[] = $emp->employee_id;
            }
            $data['category_id'] = RkNotify::CATEGORY_TIMEKEEPING;
            //set notify
            $message = Lang::get('ot::view.[Approved][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            if ($actionType == OtRegister::REJECT) {
                $message = Lang::get('ot::view.[Unapproved][Overtime] :name register overtime, from date :start_date to date :end_date', ['name' => $data['register_name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date']]);
            }
            \RkNotify::put(
                $dataReciverIds,
                $message,
                $data['link'],
                $data
            );
        }
    }

    /**
     * Cut string by words number
     * @param  string $text
     * @param  int    $limit
     * @return string
     */
    public static function limitText($text, $limit)
    {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$limit]) . '...';
        }

        return $text;
    }
}
