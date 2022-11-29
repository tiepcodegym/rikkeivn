<?php

namespace Rikkei\Core\Jobs;

use DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Rikkei\AdminSetting\Model\MobileConfig;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Notify\Model\DeviceToken;
use Rikkei\Notify\Model\Notification;
use Rikkei\Notify\Model\NotificationCategory;
use Rikkei\Notify\Model\NotifyFlag;
use Rikkei\Notify\Model\NotifyReciever;
use Rikkei\Resource\View\getOptions;

class PushNotifyToDevices extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $data;
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        if ($data['category_id'] == RkNotify::CATEGORY_HUMAN_RESOURCE || $data['category_id'] == RkNotify::CATEGORY_PROJECT) {
            $data['category_id'] = RkNotify::CATEGORY_OTHER;
        }
        $notification = null;
        if (!empty($data['is_admin'])) { // kiểm tra trường hợp admin gửi thông báo thì lưu vào bảng notifications và notify_receiver
            try {
                $mobileConfig = MobileConfig::first();
                DB::beginTransaction();
                $notificationData = [
                    'content' => $data['message'],
                    'category_id' => $data['category_id'],
                    'actor_id' => null,
                    'icon' => $mobileConfig ? $mobileConfig->avatar_url : 'avatar.png'
                ];
                $notification = Notification::create($notificationData);
                $notifyReceivers = [];
                foreach ($data['receiver_ids'] as $key => $receiverId) {
                    $notifyReceivers[$key] = [
                        'reciever_id' => $receiverId,
                        'notify_id' => $notification->id
                    ];
                }
                NotifyReciever::insert($notifyReceivers);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::info($e->getMessage());
            }
        }
        $notifyFlagTable = (new NotifyFlag())->getTable();
        $categoryName = NotificationCategory::find($data['category_id']);
        $fCMTokenData = DeviceToken::select('device_token', 'device_type')
            ->join($notifyFlagTable, function ($query) use ($notifyFlagTable, $data) {
                $query->on($notifyFlagTable . '.employee_id', '=', DeviceToken::getTableName() . '.employee_id');
                $query->where($notifyFlagTable . '.all_flg', '=', RkNotify::ON_FLAG);
                switch ($data['category_id']) {
                    case RkNotify::CATEGORY_ADMIN:
                        $query->where('notify_flags.admin_flg', '=', RkNotify::ON_FLAG);
                        break;
                    case RkNotify::CATEGORY_PERIODIC:
                        $query->where('notify_flags.period_flg', '=', RkNotify::ON_FLAG);
                        break;
                    case RkNotify::CATEGORY_TIMEKEEPING:
                        $query->where('notify_flags.timekeeping_flg', '=', RkNotify::ON_FLAG);
                        break;
                    default:
                        $query->where('notify_flags.other_flg', '=', RkNotify::ON_FLAG);
                        break;
                }
            });
        if (!empty($data['receiver_ids']) || $data['is_ot']) {
            $fCMTokenData->whereIn('device_tokens.employee_id', $data['receiver_ids']);
        }
        $fcmTokens = $fCMTokenData->groupBy('device_token')->get()->chunk(getOptions::MAX_TOKEN)->toArray();
        if ($fcmTokens) {
            $notifications = [
                'title' => $categoryName->name,
                'body' => $data['message'],
                'badge' => 1,
                'sound' => 'mySound',
                'mutable-content' => 1
            ];

            $payLoad = [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1
                    ]
                ]
            ];
            $dataFirebase = [
                'notify_id' => !empty($data['is_admin']) && $notification ? $notification->id : $data['notify_id'],
                'action' => $data['link'],
                'news_id' => $data['news_id']
            ];
            if ($data['category_id'] == RkNotify::CATEGORY_ADMIN) {
                $notifications['title'] = $data['title'];
            }

            foreach ($fcmTokens as $chunk) {
                $deviceIos = [];
                $deviceAndroids = [];
                foreach ($chunk as $key => $token) {
                    //send android
                    if ($token['device_type']) {
                        $deviceAndroids[$key] = $token['device_token'];
                    } else {
                        $deviceIos[$key] = $token['device_token'];
                    }
                }

                $url = config('services.firebase.url');
                $serverKey = config('services.firebase.key');
                $headers = [
                    'Content-Type: application/json',
                    'Authorization: key=' . $serverKey
                ];
                if ($deviceAndroids) {
                    $dataFirebase['title'] = $data['category_id'] == RkNotify::CATEGORY_ADMIN ? $data['title'] : $categoryName->name;
                    $dataFirebase['body'] = $data['message'];
                    $arrayToSend = ['registration_ids' => array_values($deviceAndroids), 'data' => $dataFirebase];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    if ($result === FALSE) {
                        \Log::info('FCM Send Error: ' . curl_error($ch));
                    }
                    \Log::info('send notification android success: ');
                }
                $arrayToSend = [
                    'registration_ids' => array_values($deviceIos),
                    'data' => $dataFirebase,
                    'notification' => $notifications,
                    'apns' => $payLoad,
                    'mutable_content' => true
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    \Log::info('FCM Send Error: ' . curl_error($ch));
                }
                \Log::info($notifications);
                \Log::info('send notification ios success: ');
            }
        }

    }
}
