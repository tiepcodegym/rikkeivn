<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\MeEvaluated;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Team\Model\EmployeeRole;
use DB;

class MeSendMailNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'me:send_mail_notify {year=0} {month=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[ME] Thông báo member chưa được đánh giá ME hoặc đã được đánh giá nhưng chưa được duyệt.';

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
        try {
            $this->info("======= Start ME send mail notify =======\n");
            Log::info("======= Start ME send mail notify =======");
            
            //Get param from command
            $year = $this->argument('year');
            $month = $this->argument('month');

            //Evaluated
            $dataEvaluated = $this->collectEvaluated($year, $month);
            $this->dataProcessing($dataEvaluated, 'evaluated');

            //Not evaluated
            $dataNotEvaluate = $this->collectNotEvaluate($year, $month);
            $this->dataProcessing($dataNotEvaluate, 'not_evaluate');

            $this->info("======= End ME send mail notify =======\n");
            Log::info("======= End ME send mail notify =======");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("======= Error ME send mail notify =======\n");
        }
    }

    public function dataProcessing($dataCollection, $type)
    {
        list($collection, $date) = $dataCollection;
        $arrTeam = [];
        foreach ($collection as $item) {
            $teamIds = $this->getTeamIds($item->email);
            $leaders = EmployeeRole::getLeader($teamIds, true);
            if ($leaders) {
                foreach ($leaders as $leader) {
                    if (!isset($arrTeam[$leader['email']])) {
                        $arrTeam[$leader['email']]['dlead'] = [
                            'team_id' => $leader['team_id'],
                            'team_name' => $leader['team_name'],
                            'name' => $leader['name'],
                            'email' => $leader['email']
                        ];
                    }
                    $arrTeam[$leader['email']]['employees'][] = [
                        'emp_name' => $item->name,
                        'emp_email' => $item->email,
                    ];
                }
            }
        }

        //Send mail
        if ($arrTeam) {
            $this->sendMail($arrTeam, $type, $date);
        }
    }

    public function sendMail($dataEmp, $type, $date)
    {
        $dateView = $date->format('m-Y');
        $filterDate = $date->format('Y-m');
        foreach ($dataEmp as $value) {
            $subject = '【ME】Thông báo số nhân viên chưa được đánh giá ME của tháng '.$dateView;
            $route = route('project::me.view.not_evaluate', ['teams' => $value['dlead']['team_id'], 'start_at' => $filterDate, 'end_at' => $filterDate]);
            if ($type == 'evaluated') {
                $subject = '【ME】Thông báo số viên chưa được duyệt ME của tháng '.$dateView;
                $route = route('project::me.view.evaluated', ['teams' => $value['dlead']['team_id'], 'eval_time' => $filterDate]);
            }
            $data = [
                'link' => $route,
                'type' => $type,
                'date' => $dateView,
                'dlead_name' => $value['dlead']['name'],
                'team_name' => $value['dlead']['team_name'],
                'employees' => $value['employees']
            ];
            $template = 'project::emails.commands.me_notification';
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($value['dlead']['email'])
                    ->setSubject($subject)
                    ->setTemplate($template, $data);
            $emailQueue->save();
        }
    }

    public function getTeamIds($email)
    {
        $teamIds = Employee::select(
            'employees.id',
            'employees.name',
            'employees.email',
            'teams.id as team_id',
            'teams.name as team_name'
        )
        ->where('employees.email', $email)
        ->join("team_members as team_mb", "team_mb.employee_id", '=', "employees.id")
        ->join("teams", "teams.id", '=', "team_mb.team_id")
        ->get()->pluck('team_id')->toArray();

        return $teamIds;
    }

    public static function collectNotEvaluate ($year = 0, $month = 0) {
        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = MeEvaluated::getTableName();
        $empTbl = Employee::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $projTeamTbl = TeamProject::getTableName();

        //collection
        $collection = ProjectMember::from($projectMemberTbl . ' as projmb')
            ->join($projectTbl . ' as proj', function ($join) {
                $join->on('projmb.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED)
                        ->whereNull('proj.deleted_at');
            })
            ->join($empTbl . ' as emp', function ($join) {
                $join->on('projmb.employee_id', '=', 'emp.id')
                        ->whereNull('emp.deleted_at');
            })
            ->leftJoin($evalTbl . ' as eval', function ($join) {
                $join->on('projmb.project_id', '=', 'eval.project_id')
                        ->on('projmb.employee_id', '=', 'eval.employee_id');
            })
            ->where('projmb.status', ProjectMember::STATUS_APPROVED)
            ->whereNotIn('proj.state', [Project::STATE_REJECT, Project::STATE_PENDING])
            ->where(function ($query) {
                $query->where(function ($query2) {
                    $query2->whereNotNull('eval.id')
                    ->where('eval.status', MeEvaluation::STT_DRAFT);
                })
                ->orWhereNull('eval.id');
            });
        
        // filter month
        if (!$year || !$month) {
            $filterMonth = Carbon::now()->subMonthNoOverflow();
        } else {
            $filterMonth = $year.'-'.$month;
        }
        if (!$filterMonth instanceof Carbon) {
            $filterMonth = Carbon::parse($filterMonth);
        }
        $date = $filterMonth;
        $collection->where('projmb.start_at', '<=', $filterMonth->lastOfMonth()->toDateTimeString());
                // ->where('projmb.end_at', '>=', $filterMonth->startOfMonth()->toDateTimeString());
        //filter employee leaved
        $collection->where(function ($query) use ($filterMonth) {
            $query->whereNull('emp.leave_date')
                ->orWhereDate('emp.leave_date', '>=', $filterMonth->startOfMonth()->toDateString());
        });

        $collection->where('projmb.end_at', '>=', $filterMonth->startOfMonth()->toDateTimeString());
        // $collection->where(DB::raw('MONTH(projmb.end_at)'), '=', $filterMonth->month)
        //     ->where(DB::raw('YEAR(projmb.end_at)'), '=', $filterMonth->year);

        // filter team
        $arrTeam = [
            Team::CODE_PREFIX_HN,
            Team::CODE_PREFIX_DN,
            Team::CODE_PREFIX_HCM,
        ];
        $teamFilter = Team::whereIn('branch_code', $arrTeam)->get()->pluck('id')->toArray();
        if ($teamFilter) {
            $collection->leftJoin($projTeamTbl . ' as tpj', function ($join) use ($teamFilter) {
                    $join->on('projmb.project_id', '=', 'tpj.project_id');
                })
                ->leftJoin($teamMemberTbl . ' as ft_tmb', 'ft_tmb.employee_id', '=', 'eval.employee_id')
                ->where(function ($query) use ($teamFilter) {
                    $query->whereIn('tpj.team_id', $teamFilter)
                            ->orWhereIn('ft_tmb.team_id', $teamFilter);
                });
        }

        $collection->orderBy('projmb.start_at', 'desc')->orderBy('projmb.project_id', 'desc');
        $collection = $collection->groupBy('projmb.project_id', 'projmb.employee_id')
                ->select('emp.id as employee_id', 'emp.employee_code', 'emp.name', 'emp.email',
                        'proj.id as project_id', 'proj.name as project_name', 'proj.project_code_auto',
                        'projmb.start_at', 'projmb.end_at')->get();

        return [$collection, $date];
    }

    public static function collectEvaluated($year = 0, $month = 0) {
        $projectMemberTbl = ProjectMember::getTableName();
        $projectTbl = Project::getTableName();
        $evalTbl = MeEvaluated::getTableName();
        $empTbl = Employee::getTableName();
        $teamProjTbl = TeamProject::getTableName();
        $teamMbTbl = TeamMember::getTableName();

        //collection
        $collection = MeEvaluated::leftJoin($projectMemberTbl.' as pjm', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'pjm.project_id')
                        ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED);
            })
            ->leftJoin($projectTbl.' as proj', function ($join) use ($evalTbl) {
                $join->on($evalTbl.'.project_id', '=', 'proj.id')
                        ->where('proj.status', '=', Project::STATUS_APPROVED)
                        ->whereNull('proj.deleted_at');
            })
            ->join($empTbl.' as emp', $evalTbl.'.employee_id', '=', 'emp.id')
            ->leftJoin(Team::getTableName() . ' as team', 'team.id', '=', $evalTbl . '.team_id')
            ->where($evalTbl.'.status', '!=', MeEvaluation::STT_REWARD);

        // check scope
        $collection->where($evalTbl.'.status', '!=', MeEvaluation::STT_DRAFT);
        
        // filter month
        if (!$year || !$month) {
            $filterMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        } else {
            $filterMonth = $year.'-'.$month;
        }
        if (!$filterMonth instanceof Carbon) {
            $filterMonth = Carbon::parse($filterMonth)->startOfMonth();
        }
        $date = $filterMonth;
        $collection->where($evalTbl.'.eval_time', $filterMonth->toDateTimeString());
        
        // filter team
        $arrTeam = [
            Team::CODE_PREFIX_HN,
            Team::CODE_PREFIX_DN,
            Team::CODE_PREFIX_HCM,
        ];
        $teamFilter = Team::whereIn('branch_code', $arrTeam)->get()->pluck('id')->toArray();
        if ($teamFilter) {
            $collection->leftJoin($teamProjTbl . ' as ft_teamproj', 'ft_teamproj.project_id', '=', 'proj.id')
                ->leftJoin($teamMbTbl . ' as ft_tmb', 'ft_tmb.employee_id', '=', $evalTbl . '.employee_id')
                ->where(function ($query) use ($teamFilter, $evalTbl) {
                    $query->whereIn('ft_teamproj.team_id', $teamFilter)
                        ->orWhereIn('ft_tmb.team_id', $teamFilter)
                        ->orWhereIn($evalTbl . '.team_id', $teamFilter);
                });
        }
        
        $collection = $collection->orderBy('eval_time', 'desc')
            ->orderBy($evalTbl.'.project_id', 'desc')        
            ->select(
                $evalTbl.'.id',
                $evalTbl.'.project_id',
                $evalTbl.'.team_id',
                $evalTbl.'.eval_time',
                $evalTbl.'.avg_point',
                $evalTbl.'.status',
                'team.name as team_name',
                'proj.start_at',
                'proj.end_at', 
                'proj.project_code_auto',
                'proj.name as project_name',
                'proj.type as project_type', 
                'emp.name',
                'emp.email',
                'emp.employee_code'
            )
            ->groupBy($evalTbl.'.id')
            ->get();
        
        return [$collection, $date];
    }
}
