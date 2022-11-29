<?php

namespace Rikkei\Notify\Classes;

use Rikkei\Core\Jobs\PushNotifyToDevices;
use Rikkei\Notify\Model\Notification;
use Rikkei\Notify\Model\NotifyReciever;
use Rikkei\Notify\Event\NotifyEvent;
use Rikkei\Notify\View\NotifyView;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RkNotify
{

    const CATEGORY_ADMIN = 1; //thông báo từ admin
    const CATEGORY_PERIODIC = 2; // thông báo định kỳ
    const CATEGORY_TIMEKEEPING = 3; // thông báo từ chấm công
    const CATEGORY_PROJECT = 4; // thông báo từ dự án
    const CATEGORY_HUMAN_RESOURCE = 5; //thông báo từ nhân sự
    const CATEGORY_OTHER = 6; // thông báo khác
    const CATEGORY_NEWS = 7; // thông báo tin tức mới
    const OFF_FLAG = 0;
    const ON_FLAG = 1;

    const GMAIL_LINK = 'https://mail.google.com/';

    /**
     * put notifications
     * @param type $recieverIds
     * @param type $content
     * @param type $link
     * @param type $data
     */
    public function put($recieverIds, $content, $link = null, $data = [])
    {
        $default = [
            'actor_id' => isset($data['actor_id']) ? $data['actor_id'] : auth()->id(),
            'schedule_code' => null,
            'icon' => null,
            'excerpt_current' => false,
            'type' => NotifyView::TYPE_MENU,
            'category_id' => self::CATEGORY_OTHER,
            'is_ot' => false,
            'type' => NotifyView::TYPE_MENU,
            'content_detail' => null
        ];
        $data = array_merge($default, $data);
            if (!is_array($recieverIds)) {
                $recieverIds = [$recieverIds];
            }
            $recieverIds = array_unique($recieverIds);
            if ($data['excerpt_current']) {
                $keyCurrent = array_search(auth()->id(), $recieverIds);
                if ($keyCurrent !== false) {
                    unset($recieverIds[$keyCurrent]);
                }
            }
        $scheduleCode = $data['schedule_code'];
        DB::beginTransaction();
        try {
            $hasNotiSchedule = false;
            if ($scheduleCode) {
                $notify = Notification::where('schedule_code', $scheduleCode)->first();
                if ($notify) {
                    $notify->update([
                        'updated_at' => Carbon::now()->toDateTimeString(),
                        'content' => $content,
                        'content_detail' => $data['content_detail'],
                        'category_id' => $data['category_id']
                    ]);
                    NotifyReciever::setNotReadOrCreate($notify->id, $recieverIds);
                    $hasNotiSchedule = true;
                }
            }
            if (!$hasNotiSchedule) {
                $saveData = [
                    'content' => $content,
                    'link' => $link,
                    'schedule_code' => $scheduleCode,
                    'actor_id' => $data['actor_id'],
                    'icon' => $data['icon'],
                    'type' => $data['type'],
                    'category_id' => $data['category_id'],
                    'content_detail' => $data['content_detail']
                ];
                $notify = Notification::create($saveData);
                $dataReciever = [];
                foreach ($recieverIds as $recieverId) {
                    $dataReciever[] = [
                        'notify_id' => $notify->id,
                        'reciever_id' => (int) $recieverId
                    ];
                }
                NotifyReciever::insert($dataReciever);
                if ($data['type'] === NotifyView::TYPE_MENU) {
                    NotifyReciever::increaseNotiNum($recieverIds);
                }
                $dataFirebase = [
                    'link' => $link,
                    'category_id' => $data['category_id'],
                    'notify_id' => $notify->id,
                    'message' => $content,
                    'receiver_ids' => $recieverIds,
                    'is_ot' => $data['is_ot']
                ];
                $this->sendNotification($dataFirebase);
            }
            DB::commit();
            event(new NotifyEvent($notify, $recieverIds));
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }

    /**
     * custom display time
     * @param type $strTime
     * @return type
     */
    public function displayDiffTime($strTime)
    {
        return NotifyView::diffTime($strTime);
    }

    /*
     * get last notification id
     */
    public function getLastId()
    {
        $maxId = Notification::from(Notification::getTableName() . ' as ntf')
                ->join(NotifyReciever::getTableName() . ' as nrc', 'ntf.id', '=', 'nrc.notify_id')
                ->where('nrc.reciever_id', auth()->id())
                ->max('ntf.id');
        return $maxId ? $maxId : 0;
    }

    /*
     * remove array element
     */
    public function removeReciverId($id, $recieverIds)
    {
        $key = array_search($id, $recieverIds);
        if ($key !== false) {
            unset($recieverIds[$key]);
        }
        return $recieverIds;
    }

    /**
     * push notification firebase
     * @param array $data
     * @return bool|string
     */
    public function sendNotification($data = [])
    {
        $default = [
            'message' => '',
            'link' => null,
            'notify_id' => null,
            'category_id' => self::CATEGORY_OTHER,
            'news_id' => null,
            'receiver_ids' => [],
            'delay' => 0,
            'queue' => 'default',
            'title' => null
        ];
        $data = array_merge($default, $data);
        $categoriesType = [self::CATEGORY_ADMIN, self::CATEGORY_TIMEKEEPING, self::CATEGORY_NEWS, self::CATEGORY_OTHER];
        if (in_array($data['category_id'], $categoriesType)){
            $jobId = dispatch((new PushNotifyToDevices($data))->delay($data['delay'])->onQueue($data['queue']));
            return $jobId;
        }
        return null;
    }

    /**
     * render view mail luu vao cot content_detail
     * @param $template
     * @param $data
     * @return string $contentDetail
     */
    public static function renderSections($template, $data)
    {
        $contentDetail = view($template, compact('data'))->renderSections();
        return !empty($contentDetail['content']) ? $contentDetail['content'] : null;
    }

}
