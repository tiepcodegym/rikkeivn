<?php

namespace Rikkei\Test\Listener;

use Rikkei\Test\Event\SubmitTestEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Rikkei\Test\Models\Result;
use Illuminate\Support\Facades\DB;

class SyncSubmitTests implements ShouldQueue
{
    public function handle(SubmitTestEvent $event)
    {
        DB::beginTransaction();
        try {
            $result = Result::find($event->resultId);
            if ($result) {
                $result->syncResultLang();
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info('Error sync test result');
            \Log::info($ex);
        }
    }
}
