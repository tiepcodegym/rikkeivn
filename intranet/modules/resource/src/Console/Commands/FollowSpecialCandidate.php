<?php

namespace Rikkei\Resource\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Team\Model\Employee;
use RkNotify;

class FollowSpecialCandidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candidate:follow-special';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thông báo theo dõi ứng viên thuộc vùng quan tâm đến HR phụ trách';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::now()->toDateString();
        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $candidates = Candidate::join($tblEmployee, "{$tblEmployee}.email", '=', "{$tblCandidate}.recruiter")
            ->select(
                "{$tblCandidate}.id AS candidate_id",
                "{$tblCandidate}.status_update_date",
                "{$tblEmployee}.id AS recruiter_id",
                "{$tblCandidate}.recruiter",
                "{$tblCandidate}.interested"
            )
            ->whereIn("{$tblCandidate}.status", [getOptions::FAIL, getOptions::FAIL_CDD])
            ->whereIn("{$tblCandidate}.interested", [getOptions::INTERESTED_LESS, getOptions::INTERESTED_NORMAL, getOptions::INTERESTED_SPECIAL])
            ->whereDate("{$tblCandidate}.status_update_date", '<', $now)
            ->get();

        foreach ($candidates as $key => $candidate) {
            $date = View::getNotifyInterestedDate($candidate->status_update_date, (int)$candidate->interested);
            if ($now !== $date->toDateString()) {
                $candidates->forget($key);
            }
        }
        $recruiters = $candidates->groupBy('recruiter_id');
        foreach ($recruiters as $empId => $recruiter) {
            $content = Lang::get("resource::message.[Notification] Remind :total interested candidates", ['total' => count($recruiter)]);
            $link = route('resource::candidate.interested');
            RkNotify::put($empId, $content, $link, ['category_id' => \Rikkei\Notify\Classes\RkNotify::CATEGORY_HUMAN_RESOURCE]);
        }
    }
}
