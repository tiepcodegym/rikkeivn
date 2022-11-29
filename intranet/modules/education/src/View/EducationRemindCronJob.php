<?php

namespace Rikkei\Education\View;

use Mail;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\SettingTemplateMail;
use Rikkei\Team\Model\Employee;


class EducationRemindCronJob
{
    const FORM_IMPORT_LINK = 'https://docs.google.com/spreadsheets/d/10IEZWUfMwTi9x3am-sbqTTQqYjD1m-VRUfRfCWcIRDQ/edit?usp=sharing';

    public static function sendMailRemind()
    {
        // Get current time
        $now = Carbon::now();

        // add 2 hours
        $addHours = $now->addHour(2)->toDateTimeString();
        $coursesTable = EducationCourse::getTableName();
        $classTable = EducationClass::getTableName();
        $classDetailTable = EducationClassDetail::getTableName();
        $classShiftTable = EducationClassShift::getTableName();
        $employeeTable = Employee::getTableName();
        $response = EducationCourse::join($classTable, $coursesTable . '.id', '=', $classTable . '.course_id')
            ->join($classDetailTable, $classDetailTable . '.class_id', '=', $classTable . '.id')
            ->join($classShiftTable, $classShiftTable . '.class_id', '=', $classTable . '.id')
            ->join($employeeTable, $employeeTable . '.id', '=', $classDetailTable . '.employee_id')
            ->where($coursesTable . '.status', 3)
            ->where($classDetailTable . '.is_mail_sent', 0)
            ->where($classShiftTable . '.start_date_time', '<=', $addHours)
            ->select([$classDetailTable . '.id as class_detail_id', $employeeTable . '.id', $employeeTable . '.name', $employeeTable . '.email', $classTable . '.class_name', $classShiftTable . '.start_date_time as time', $classShiftTable . '.location_name as location'])
            ->get()
            ->toArray();
        if (isset($response) && !empty($response)) {
            $data['global_subject'] = trans('education::mail.Reminder start class');
            $data['global_view'] = 'education::template-mail.education-remind';
            $template = SettingTemplateMail::where('template', $data['global_view'])->first();
            $data['global_content'] = isset($template->description) && !empty($template->description) ? $template->description : "";
//        $data['global_link'] =  URL::route('education::education.detail', ['id' => $value['course_id'], 'flag' => '0#infomation_tab']);
            $data['global_item'] = $response;
            $patternsArr = ['/\{\{\sname\s\}\}/', '/\{\{\sclass\s\}\}/', '/\{\{\stime\s\}\}/', '/\{\{\slocation\s\}\}/'];
            $replacesArr = ['name', 'class_name', 'time', 'location'];
            self::pushNotificationAndEmail($data, $patternsArr, $replacesArr);
        }
    }

    /**
     * Push Notification or Email
     * @param [array] $data
     * @param [array] $patternsArr
     * @param [array] $replacesArr
     * @return boolean
     */
    public static function pushNotificationAndEmail(array $data, array $patternsArr, array $replacesArr) {
        try {
            $dataInsert = [];
            $receiverIds = [];
            $classDetailIds = [];
            foreach ($data['global_item'] as $item) {
                $receiverIds[] = $item['id'];
                $classDetailIds[] = $item['class_detail_id'];
                $newReplaceArr = [];
                foreach ($replacesArr as $index) {
                    if (array_key_exists($index, $item)) {
                        $newReplaceArr[] = $item[$index];
                    } else {
                        if (array_key_exists($index, $data)) {
                            $newReplaceArr[] = $data[$index];
                        }
                    }
                }
                $subject = preg_replace($patternsArr, $newReplaceArr, $data['global_subject']);
                $content = preg_replace($patternsArr, $newReplaceArr, $data['global_content']);

                // Check isset, send mail
                if (isset($item['email']) && !empty($item['email'])) {
                    $templateData = [
                        'reg_replace' => [
                            'patterns' => $patternsArr,
                            'replaces' => $newReplaceArr
                        ],
                        'content' => $content
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item['email'], $item['name'])
                        ->setSubject($subject)
                        ->setTemplate($data['global_view'], $templateData);
                    $dataInsert[] = $emailQueue->getValue();
                }

                // Send notification
//                \Rikkei\Notify\Facade\RkNotify::put(
//                    $item['id'],
//                    $subject,
//                    $data['global_link'],
//                    ['actor_id' => null, 'icon' => 'reward.png']
//                );
            }

            $result = EmailQueue::insert($dataInsert);

            // update reminded case
            if ($result) {
                EducationClassDetail::whereIn('id', $classDetailIds)->update(['is_mail_sent' => 1]);
            }

            return true;
        } catch (Exception $ex) {
            Log::info($ex);
        }

        return false;
    }
}
