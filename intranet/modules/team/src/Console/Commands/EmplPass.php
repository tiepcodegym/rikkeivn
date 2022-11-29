<?php

namespace Rikkei\Team\Console\Commands;

use Illuminate\Console\Command;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeSetting;

class EmplPass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'empl:pass {--email=} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get employee pass';

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
        $employee = null;
        if ($email = $this->option('email')) {
            $employee = Employee::select(['id', 'name', 'email'])
                ->where('email', '=', $email)
                ->first();
        } else if ($id = $this->option('id')) {
            $employee = Employee::select(['id', 'name', 'email'])
                ->where('id', '=', $id)
                ->first();
        } else {
            $employee = null;
        }
        if (!$employee) {
            return $this->error('Not found employee');
        }
        $value = EmployeeSetting::getKeyValue($employee->id, EmployeeSetting::KEY_PASS_FILE);
        if (!$value) {
            return $this->error('Not found pass');
        }
        $this->info(sprintf('Pass of id - %s; name - %s; email - %s: ', $employee->id, $employee->name, $employee->email));
        $this->info(decrypt($value));
    }
}
