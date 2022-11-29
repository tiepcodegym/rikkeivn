<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\Project\View\ProjectGitlab;
use Rikkei\Core\Model\EmailQueue;

class ChangeUsernameGit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:change_username  {page=1} {per_page=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change git username if username != email';

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
            $page = (int)$this->argument('page');
            $perPage = (int)$this->argument('per_page');
            $usersChange = $git->getAccUsernameNotEqualEmail($page, $perPage);
            $this->info("======= Start change git username =======\n");
            $bar = $this->output->createProgressBar(count($usersChange));
            foreach ($usersChange as $user) {
                $account = strtolower(preg_replace('/@.*$/', '', $user['email']));
                if ($git->hasUsernameExist($account)) {
                    continue;
                }
                $git->changeUsername($user);
                $this->sendMail($user);
                $bar->advance();
                \Log::info("======= Git changed username " . strtolower($user['username']) . " to " . strtolower($account) . " =======\n");
            }
            $bar->finish();
            $this->info("\n======= End change git username =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("\n======= Error change git username =======\n");
        }
    }

    public function sendMail($user)
    {
        $dataMail = [
            'name' => $user['name'],
            'old_user' => $user['username'],
            'new_user' => $account = strtolower(preg_replace('/@.*$/', '', $user['email'])),
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($user['email'])
                ->setSubject('[Intranet] Thay đổi gitlab username do đặt sai chuẩn')
                ->setTemplate('project::emails.commands.change_git_username', $dataMail)
                ->save();
    }
}
