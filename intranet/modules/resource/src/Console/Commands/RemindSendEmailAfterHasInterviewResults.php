<?php

namespace Rikkei\Resource\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Resource\View\getOptions;
use RkNotify;
use DB;
use Illuminate\Support\Facades\Lang;

class RemindSendEmailAfterHasInterviewResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interview-result:remind-send-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nhắc lịch HR phụ trách gửi email sau khi đã có kết quả phỏng vấn';

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
        $tblEmployee = Employee::getTableName();
        $tblCandidate = Candidate::getTableName();
        $tblCandidateMail = CandidateMail::getTableName();
        $interviewedCandidates = Candidate::join($tblEmployee, "{$tblEmployee}.email", '=', "{$tblCandidate}.recruiter")
            ->leftJoin($tblCandidateMail, function ($query) use ($tblCandidate, $tblCandidateMail) {
                $query->on("{$tblCandidateMail}.candidate_id", '=', "{$tblCandidate}.id")
                    ->where(function ($subQuery) use ($tblCandidateMail, $tblCandidate) {
                        $subQuery
                            ->where(function ($subQuery2) use ($tblCandidateMail, $tblCandidate) {
                                $subQuery2->where("{$tblCandidate}.status", '=', getOptions::OFFERING)
                                    ->whereIn("{$tblCandidateMail}.type", Candidate::listMailOffers());
                            })
                            ->orWhere(function ($subQuery2) use ($tblCandidate, $tblCandidateMail) {
                                $subQuery2->where("{$tblCandidate}.interview_result", '=', getOptions::RESULT_FAIL)
                                    ->whereIn("{$tblCandidateMail}.type", Candidate::listMailInterviewFails());
                            });
                    });
            })
            ->whereNull("{$tblCandidateMail}.type")
            ->where(function ($query) use ($tblCandidate) {
                $query->where("{$tblCandidate}.status", '=', getOptions::OFFERING)
                    ->orWhere("{$tblCandidate}.interview_result", '=', getOptions::RESULT_FAIL);
            })
            ->whereIn(DB::raw("DATE({$tblCandidate}.status_update_date)"), [Carbon::now()->subDay(3)->toDateString(), Carbon::now()->subDay(2)->toDateString()])
            ->groupBy("{$tblCandidate}.id")
            ->select(
                "{$tblCandidate}.id AS candidate_id",
                "{$tblCandidate}.status_update_date",
                "{$tblEmployee}.id AS recruiter_id",
                "{$tblCandidate}.recruiter"
            )
            ->get();

        $recruiters = $interviewedCandidates->groupBy('recruiter_id');
        foreach ($recruiters as $empId => $recruiter) {
            $content = Lang::get("resource::message.[Notification] Request to send mail to :total candidates had interview results", ['total' => count($recruiter)]);
            $link = route('resource::candidate.follow', ['type' => getOptions::TYPE_REMIND_SEND_MAIL_OFFER]);
            RkNotify::put($empId, $content, $link, ['category_id' => \Rikkei\Notify\Classes\RkNotify::CATEGORY_PROJECT]);
        }
    }
}
