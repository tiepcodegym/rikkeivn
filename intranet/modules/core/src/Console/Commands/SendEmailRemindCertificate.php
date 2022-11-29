<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;

class SendEmailRemindCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind {type=certificate : Fill type for send email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email for employee has not added a certificate.';

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
        if (!$this->confirm('Do you want send email to list employees not added certificate?')) {
            return;
        }

        $type = $this->argument('type');
        if (is_numeric($type)) {
            $this->error("Please fill in a string. \n");
        } else {
            try {
                $this->line("======= Start send email =======\n");
                $employeeNotAddedCertificate = Employee::leftJoin('employee_certies', 'employees.id', '=', 'employee_certies.employee_id')
                    ->where('employee_certies.id', null)
                    ->where(function ($query) {
                        $query->orWhereNull('employees.leave_date')
                            ->orWhereDate('employees.leave_date', '>', date('Y-m-d'));
                    })
                    ->select('employees.name', 'employees.email')
                    ->get();
                if ($employeeNotAddedCertificate) {
                    $bar = $this->output->createProgressBar(count($employeeNotAddedCertificate));
                    $subject = '[Intranet] Nhắc nhở thêm chứng chỉ vào profile';
                    $dataEmail = [];
                    foreach ($employeeNotAddedCertificate as $item) {
                        $mail = new EmailQueue();
                        $mail->setTo($item->email, $item->name)
                            ->setTemplate('core::emails.remind.certificate', [
                                'name' => $item->name
                            ])
                            ->setSubject($subject);
                        $dataEmail[] = $mail->getValue();
                        $bar->advance();
                    }
                    EmailQueue::insert($dataEmail);
                    $bar->finish();
                    $this->info("\n\n======= Completed =======\n");
                } else {
                    $this->info("======= All employees have added the certificate =======\n");
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage() . "\n");
            }
        }
    }
}
