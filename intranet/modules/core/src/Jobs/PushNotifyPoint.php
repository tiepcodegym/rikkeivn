<?php

namespace Rikkei\Core\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Notify\Model\DeviceToken;
use Rikkei\Notify\Model\NotificationCategory;
use Rikkei\Notify\Model\NotifyFlag;
use Rikkei\Resource\View\getOptions;

class PushNotifyPoint extends Job implements ShouldQueue
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
        $notifyFlagTable = (new NotifyFlag())->getTable();
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
                'title' => $data['point_name'],
                'body' => $data['content']
            ];
            $dataFireBase = [
                'title' => $data['point_name'],
                'body' => $data['content'],
                'reward_point' => $data['reward_point'],
                'total_point' => null,
                'type' => !empty($data['type']) ? $data['type'] : 'reward_point_no_dialog'
            ];
            $url = config('services.firebase.url');
            $serverKey = config('services.firebase.key');
            $headers = [
                'Content-Type: application/json',
                'Authorization: key=' . $serverKey
            ];
            foreach ($fcmTokens as $deviceToken) {
                $deviceIos = [];
                $deviceAndroids = [];
                foreach ($deviceToken as $key => $token) {
                    //send android
                    if ($token['device_type']) {
                        $deviceAndroids[$key] = $token['device_token'];
                    } else {
                        $deviceIos[$key] = $token['device_token'];
                    }
                }
                if ($deviceAndroids) {
                    $tokens = array_values($deviceAndroids);
                    $arrayToSend = ['registration_ids' => $tokens, 'data' => $dataFireBase];
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
                $arrayToSend = ['registration_ids' => array_values($deviceIos), 'data' => $dataFireBase, 'notification' => $notifications];
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
                \Log::info('push message success');
            }

        }
    }
}
