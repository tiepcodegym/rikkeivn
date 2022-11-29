<?php

namespace Rikkei\Notify\Event;

use Rikkei\Core\Events\Event;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

//class NotifyEvent extends Event implements ShouldBroadcast
class NotifyEvent extends Event
{

    use SerializesModels;

    public $notify;
    public $recieverIds;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notify, $recieverIds = [])
    {
        $noti = [
            'id' => $notify->id,
            'content' => $notify->content,
            'time' => $notify->updated_at->timestamp,
            'link' => $notify->getLink(),
            'icon' => $notify->icon,
            'type' => $notify->type,
            'actor_id' => $notify->actor_id,
        ];
        $this->notify = $noti;
        if (!is_array($recieverIds)) {
            $recieverIds = [$recieverIds];
        }
        $this->recieverIds = $recieverIds;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['notify-channel'];
    }

}
