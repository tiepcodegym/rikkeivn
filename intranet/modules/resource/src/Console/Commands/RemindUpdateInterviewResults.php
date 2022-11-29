<?php

namespace Rikkei\Resource\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use RkNotify;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View;
use DB;
use Rikkei\Team\Model\Employee;

class RemindUpdateInterviewResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interview-result:remind-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nhắc lịch HR phụ trách cập nhật kết quả phỏng vấn';

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
        $date4DaysAgo = Carbon::now()->subDay(4)->toDateString();
        $date5DaysAgo = Carbon::now()->subDay(5)->toDateString();

        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $interviewingCandidates = Candidate::join($tblEmployee, "{$tblEmployee}.email", '=', "{$tblCandidate}.recruiter")
            ->select(
                "{$tblEmployee}.id AS employee_id",
                "{$tblCandidate}.id AS candidate_id",
                "{$tblCandidate}.recruiter"
            )
            ->where("{$tblCandidate}.status", getOptions::INTERVIEWING)
            ->whereRaw('CASE'
                . " WHEN {$tblCandidate}.interview2_plan IS NOT NULL AND DATE({$tblCandidate}.interview2_plan) <> '0000-00-00'"
                . " THEN DATE({$tblCandidate}.interview2_plan) IN ('{$date4DaysAgo}', '{$date5DaysAgo}')"
                . " ELSE DATE({$tblCandidate}.interview_plan) IN ('{$date4DaysAgo}', '{$date5DaysAgo}')"
                . ' END'
            )
            ->get();

        $recruiters = $interviewingCandidates->groupBy('employee_id');
        foreach ($recruiters as $empId => $recruiter) {
            $content = Lang::get("resource::message.[Notification] Request to update results of :total candidates had interview results", ['total' => count($recruiter)]);
            $link = route('resource::candidate.follow');
            RkNotify::put($empId, $content, $link, ['category_id' => \Rikkei\Notify\Classes\RkNotify::CATEGORY_PROJECT]);
        }
    }
}
