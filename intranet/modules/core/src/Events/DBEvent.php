<?php

namespace Rikkei\Core\Events;

use Rikkei\Core\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DBEvent extends Event
{
    use SerializesModels;

    public $action;
    public $model;
    public $attributes;
    public $isSave;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($action, $model, $attributes = [], $isSave = true)
    {
        $this->action = $action;
        $this->model = $model;
        $this->attributes = $attributes;
        $this->isSave = $isSave;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
