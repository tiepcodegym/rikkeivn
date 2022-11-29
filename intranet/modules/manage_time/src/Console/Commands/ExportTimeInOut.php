<?php

namespace Rikkei\ManageTime\Console\Commands;

use Illuminate\Console\Command;
use Rikkei\ManageTime\View\ViewTimeKeeping;

class ExportTimeInOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:exporttime {date=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export time in table manages_time_timekeepings become csv file. Param date format Y-m-d, nullable';

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
        $date = $this->argument('date');
        $view = new ViewTimeKeeping();
        $view->cronExportTimeInOut($date);
    }
}
