<?php

namespace Rikkei\Notify\Listeners;

use Rikkei\Notify\Event\NotifyEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuzzleHttp\Client;
use Rikkei\Core\Model\User;
use Rikkei\Notify\View\NotifyView;

/**
 * listener for event NotifyEvent
 *
 * @author lamnv
 */
class NotifyCreated implements ShouldQueue
{
    public function handle(NotifyEvent $event)
    {
        $client = new Client();
        $method = 'POST';
        $url = trim(config('notify.host'), '/') . '/' . trim(config('notify.api_uri'), '/');
        $notify = $event->notify;
        if (isset($notify['actor_id']) && $notify['actor_id']) {
            $actorUser = User::find($notify['actor_id']);
            $notify['image'] = $actorUser ? $actorUser->avatar_url : null;
        }

        try {
            $client->request($method, $url, [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(config('notify.auth.username') . ':' . config('notify.auth.password')),
                ],
                'body' => json_encode([
                    'employee_ids' => $event->recieverIds,
                    'title' => $notify['content'],
                    'action' => $notify['link'] ? $notify['link'] : '#',
                    'time' => $notify['time'],
                    'image' => NotifyView::getImage($notify),
                    'type' => $notify['type'],
                    'notify_id' => $notify['id'],
                    'app_env' => config('notify.noti_env')
                ])
            ]);
        } catch (\Exception $ex) {
            \Log::info('Error push notify');
            \Log::info($ex);
        }
    }
}
