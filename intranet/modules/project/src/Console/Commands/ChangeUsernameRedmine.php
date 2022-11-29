<?php

namespace Rikkei\Project\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rikkei\CallApi\Helpers\Redmine;
use Rikkei\Core\Model\EmailQueue;

class ChangeUsernameRedmine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redmine:change_username';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change redmine username if username != email. Effect only @rikkeisoft';

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
            $redmine = new Redmine();
            $usersChange = $redmine->getAccUsernameNotEqualEmail();
            $this->info("======= Start change redmine username =======\n");
            $bar = $this->output->createProgressBar(count($usersChange));
            foreach ($usersChange as $user) {
                $account = strtolower(preg_replace('/@.*$/', '', $user['mail']));
                $redmine->updateUsername($user);
                $this->sendMail($user);
                $bar->advance();
                \Log::info("======= Redmine changed username " . strtolower($user['login']) . " to " . strtolower($account) . " =======\n");
            }
            $bar->finish();
            $this->info("\n======= End change redmine username =======\n");
        } catch (\Exception $ex) {
            Log::info($ex);
            $this->info("\n======= Error change redmine username =======\n");
        }
    }

    public function sendMail($user)
    {
        $dataMail = [
            'name' => $user['firstname'] . ' ' . $user['lastname'],
            'old_user' => $user['login'],
            'new_user' => strtolower(preg_replace('/@.*$/', '', $user['mail'])),
        ];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($user['mail'])
                ->setSubject('[Rikkeisoft Intranet] Thay đổi redmine username do đặt sai chuẩn')
                ->setTemplate('project::emails.commands.change_redmine_username', $dataMail)
                ->save();
    }
}
