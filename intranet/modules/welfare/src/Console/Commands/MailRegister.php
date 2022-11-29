<?php

namespace Rikkei\Welfare\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\WelfareFile;
use Rikkei\Welfare\Model\Event;

class MailRegister extends Command
{
     /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'welfare:confirm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to the welfare program for the employee';

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
        $event = Event::whereBetween('start_at_register', [
                Carbon::now()->format('Y-m-d 00:00:00'),
                Carbon::now()->format('Y-m-d 23:59:59')
            ])
            ->get();

        foreach ($event as $item) {
            if ($item->is_send_mail_auto == Event::SEND_MAIL_AUTO) {
                $data = [];
                $data['subject'] = Lang::get('welfare::view.Subject Email');
                $data['content'] = Event::getBasicInformation($item->id)->toArray();
                $welFile = WelfareFile::getFileByEvent($item->id);
                if (isset($welFile) && count($welFile)) {
                    foreach ($welFile as $key) {
                        $path = storage_path('app/' . WelfareFile::ACCESS_FILE . '/' . $key->files);
                        if (isset($path)) {
                            $data['attachment'][] = $path;
                        }
                    }
                }
                $listEmail = array_unique(WelEmployee::getEmailEmplByWelId($item->id));

                if (1 < count($listEmail) && count($listEmail) <= 15) {
                    $data['email'] = $listEmail[0];
                    unset($listEmail[0]);

                    foreach ($listEmail as $key) {
                        $data['email_bcc'][] = $key;
                    }

                    $this->pushEmailToQueue($data);
                } elseif (count($listEmail) > 15) {
                    $array = [];
                    $emailGr = array_chunk($listEmail, 15);

                    foreach ($emailGr as $key => $value) {
                        $data['email'] = $value[0];
                        unset($value[0]);
                        $data['email_bcc'] = $value;
                        $array[] = $this->pushEmailToArray($data);
                    }

                    EmailQueue::insert($array);
                }
                $this->info('messages sent successfully!');
            }
        }
    }

    /**
     *
     * @param array $data
     * @return boolean
     */
    public function pushEmailToQueue($data)
    {
        $template = 'welfare::template.mail';
        $subject = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['email'])
            ->setSubject($subject)
            ->setTemplate($template, $data['content']);
        if (isset($data['email_bcc'])) {
            foreach ($data['email_bcc'] as $key) {
                $emailQueue->addBcc($key);
            }
        }
        if (isset($data['attachment'])) {
            foreach ($data['attachment'] as $key) {
                $emailQueue->addAttachment($key, false);
            }
        }

        try {
            $emailQueue->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function pushEmailToArray($data)
    {
        $template = 'welfare::template.mail';
        $subject = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['email'])
            ->setSubject($subject)
            ->setTemplate($template, $data['content']);
        if (isset($data['email_bcc'])) {
            foreach ($data['email_bcc'] as $key) {
                $emailQueue->addBcc($key);
            }
        }
        if (isset($data['attachment'])) {
            foreach ($data['attachment'] as $key) {
                $emailQueue->addAttachment($key, false);
            }
        }

        return $emailQueue->getValue();
    }

}
