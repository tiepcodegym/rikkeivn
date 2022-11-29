<?php

namespace Rikkei\Core\Listeners;

use Rikkei\Core\Events\DBEvent;
use Rikkei\Core\Model\DBLog;
use Rikkei\Api\Sync\BaseSync;

class DBLogListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DBEvent  $event
     * @return void
     */
    public function handle(DBEvent $event)
    {
        if (in_array($event->model, ['Rikkei\Core\Model\DBLog', 'db_logs'])) {
            return;
        }
        try {
            $listTables = DBLog::getSaveLogTables();
            $attributes = $event->attributes;
            if ($event->isSave && in_array($event->model, $listTables)) {
                DBLog::create([
                    'action' => $event->action,
                    'model' => $event->model,
                    'subject_id' => isset($attributes['id']) ? $attributes['id'] : null,
                    'attributes' => json_encode($attributes),
                    'actor_id' => auth()->id()
                ]);
            }

            //call sync api
            $action = $event->action;
            if (isset($attributes['new']['deleted_at'])) {
                $action = 'deleted';
            }
            BaseSync::call($event->model, $action, $attributes);
        } catch (\Exception $ex) {
            \Log::info($ex);
        }
    }
}
