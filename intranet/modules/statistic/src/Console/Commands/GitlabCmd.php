<?php

namespace Rikkei\Statistic\Console\Commands;

use Illuminate\Console\Command;
use Rikkei\Statistic\Helpers\STProjLocHelper;
use Rikkei\Statistic\Helpers\STProjBugHelper;
use Rikkei\Statistic\Helpers\STProjHelper;

class GitlabCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitlab:loc {--bug} {--loc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gitlab get loc, bug of project, employee';

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
        if ($this->option('bug')) {
            STProjBugHelper::getInstance()->processBugAll();
            return $this->info('Count bug exec success');
        }
        if ($this->option('loc')) {
            STProjLocHelper::getInstance()->processLocAll();
            return $this->info('Gitlab exec success');
        }
        STProjHelper::processAllSTProj();
        return $this->info('Project statistic exec success');
    }
}
