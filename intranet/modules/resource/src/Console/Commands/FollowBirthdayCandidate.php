<?php

namespace Rikkei\Resource\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use RkNotify;

class FollowBirthdayCandidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candidate:follow-birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thông báo sắp đến ngày sinh nhật của ứng viên thuộc vùng quan tâm đến HR phụ trách';

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
        $now = Carbon::now();
        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $candidates = Candidate::join($tblEmployee, "{$tblEmployee}.email", '=', "{$tblCandidate}.recruiter")
            ->select(
                "{$tblCandidate}.id AS candidate_id",
                "{$tblCandidate}.birthday",
                "{$tblEmployee}.id AS recruiter_id",
                "{$tblCandidate}.recruiter"
            )
            ->whereIn("{$tblCandidate}.status", [getOptions::FAIL, getOptions::FAIL_CDD])
            ->whereIn("{$tblCandidate}.interested", [getOptions::INTERESTED_LESS, getOptions::INTERESTED_NORMAL, getOptions::INTERESTED_SPECIAL])
            ->where("{$tblCandidate}.birthday", '<>', '0000-00-00')
            ->whereDate("{$tblCandidate}.birthday", '<', $now->toDateString())
            ->get();

        foreach ($candidates as $key => $candidate) {
            $birthday = Carbon::parse($candidate->birthday);
            $birthday->addYear($now->year - $birthday->year);
            if ($birthday->format('m-d') <= '01-07') {
                $birthday->addYear($now->year - $birthday->year + 1);
            } else {
                $birthday->addYear($now->year - $birthday->year);
            }
            if ($now->toDateString() !== $birthday->subDay(7)->toDateString()) {
                $candidates->forget($key);
            }
        }
        $recruiters = $candidates->groupBy('recruiter_id');
        foreach ($recruiters as $empId => $recruiter) {
            $content = Lang::get("resource::message.[Notification] Upcoming birthday of :total interested candidates", ['total' => count($recruiter)]);
            $link = route('resource::candidate.interested', ['type' => 'birthday']);
            RkNotify::put($empId, $content, $link, ['category_id' => \Rikkei\Notify\Classes\RkNotify::CATEGORY_HUMAN_RESOURCE]);
        }
    }
}
