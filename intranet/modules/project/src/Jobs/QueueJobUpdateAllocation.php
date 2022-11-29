<?php

namespace Rikkei\Project\Jobs;

use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Rikkei\Api\Helper\HrmFo;
use Rikkei\Core\Jobs\Job;
use Rikkei\Project\Model\CronjobProjectAllocations;


class QueueJobUpdateAllocation extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $year;
    private $branchCode;
    private $request;
    private $teamId;

    public function __construct($request, $year, $branchCode = null, $teamId = null)
    {
        $this->request = $request;
        $this->year = $year;
        $this->branchCode = $branchCode;
        $this->teamId = $teamId;
    }

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1200;


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::debug("==========================[{$this->year}] Start Allocation: Branch: {$this->branchCode}, Team Id: {$this->teamId}");
            ini_set('max_execution_time', '1200');
            $hrmFoInstance = new HrmFo;
            $allocationData = $hrmFoInstance->getFoAllocation($this->request);
            $data = [
                'year' => $this->year,
                'team_id' => $this->teamId,
                'branch_code' => $this->branchCode,
                'allocation_serialize' => serialize($allocationData)
            ];
            CronjobProjectAllocations::create($data);
            Log::debug("==========================[{$this->year}] End Allocation Branch: {$this->branchCode}, Team Id: {$this->teamId}");

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw $exception;
        }
    }
}