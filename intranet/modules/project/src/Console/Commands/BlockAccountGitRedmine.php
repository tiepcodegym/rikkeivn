<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Team\Model\Team;
use Rikkei\CallApi\Helpers\Redmine;

class BlockAccountGitRedmine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:block_account_git_redmine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blog account git, redmine employee leave';

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
            $git = ProjectGitlab::getInstance();
            $redmine = new Redmine();
            $employees = Team::getMemberGridData(null, Team::END_WORK, null, ['del_account' => true]);
            $this->info("======= Start Blog Account Gitlab and Redmine =======\n");
            $bar = $this->output->createProgressBar(count($employees));
            foreach ($employees as $employee) {

                $account = strtolower(preg_replace('/@.*$/', '', $employee->email));
                $git->blogAccountGit($account);
                if ($account != 'hoadt') {
                    $redmine->blogAccount($account);
                }

                $bar->advance();
            }
            $bar->finish();
            $this->info("======= End Blog Account Gitlab and Redmine =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("======= Error Blog Account Gitlab and Redmine =======\n");
        }
    }
}
