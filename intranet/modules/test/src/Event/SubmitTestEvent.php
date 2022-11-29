<?php

namespace Rikkei\Test\Event;

use Rikkei\Core\Events\Event;
use Illuminate\Queue\SerializesModels;

class SubmitTestEvent extends Event
{

    use SerializesModels;

    public $resultId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($resultId)
    {
        $this->resultId = $resultId;
    }

}
