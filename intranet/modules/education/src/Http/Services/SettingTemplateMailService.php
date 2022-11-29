<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\SettingTemplateMail;
use Exception;

class SettingTemplateMailService
{
    protected $modelTemplateMail;

    public function __construct(SettingTemplateMail $modelTemplateMail)
    {
        $this->modelTemplateMail = $modelTemplateMail;
    }

    public function getItem($type)
    {
        return $this->modelTemplateMail->where('name', $type)->first();
    }

    public function updateTemplateMail($request)
    {
        $collection = $this->modelTemplateMail->where('template', $request->template)->first();
        if ($collection) {
            $collection->fill($request->all());

            return $collection->save();
        }

        return $this->modelTemplateMail->create($request->all());
    }

    /**
     * Danh sách học viên sắp vào học
     * @return array
     */
    public static function listClassNotify()
    {
        $listClass = EducationClassShift::listClassNotify();
        $listEmployees = array();
        foreach ($listClass as $class) {
            $classDetail = EducationClassDetail::listEmployees($class);
            $listEmployees = array_merge($listEmployees, $classDetail);
        }

        return $listEmployees;

    }

    /**
     * @param $listEmployees
     * @throws Exception
     */
    public static function sendEmailReminder($listEmployees)
    {
        $template = SettingTemplateMail::where('name', SettingTemplateMail::TEMPLATE_REMINDER)->first();
        $dataInsert = array();
        $classDetailIds = array();
        $patternsArray = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\stime\s\}\}/',
            '/\{\{\slocation\s\}\}/',
            '/\{\{\sclass\s\}\}/',
        ];
        $subject = trans('education::view.Template Email reminder form to join the course');
        foreach ($listEmployees as $item) {
            if (isset($item['email']) && $item['email']) {
                $emailQueue = new EmailQueue();
                $replacesArray = [
                    $item['name'],
                    $item['time'],
                    $item['location'],
                    $item['class'],
                ];
                $dataContent = array(
                    'content' => $template->description,
                    'reg_replace' => [
                        'patterns' => $patternsArray,
                        'replaces' => $replacesArray
                    ],
                );

                $emailQueue->setTo($item['email'], $item['name'])
                    ->setSubject($subject)
                    ->setTemplate($template->template, $dataContent);
                $dataInsert[] = $emailQueue->getValue();
                $classDetailIds[] = $item['class_detail_id'];
            }
        }

        DB::beginTransaction();
        try {
            $result = EmailQueue::insert($dataInsert);
            if ($result) {
                EducationClassDetail::updateSendedRemind($classDetailIds);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::error($ex);
        }
    }

    /**
     * cron reminder education
     * @throws Exception
     */
    public static function cronSendMailReminder()
    {
        $listEmployees = self::listClassNotify();
        static::sendEmailReminder($listEmployees);
    }
}
