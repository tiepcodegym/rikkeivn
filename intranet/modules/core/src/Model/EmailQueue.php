<?php

namespace Rikkei\Core\Model;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Swift_Mime_SimpleMessage;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Employee;
use Swift_SmtpTransport;
use Swift_Mailer;
use Rikkei\Core\View\View as ViewCore;
use RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use PHPMailer;

class EmailQueue extends CoreModel
{
    protected $table = 'email_queues';
    public $timestamps = false;

    const FOLDER_PROCESS = 'process';
    const FILE_PROCESS = 'email_queue';
    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';
    const APP_PASS_LENGTH = 16;
    const UPLOAD_FILE_FOLDER = 'email_queue/';

    protected $toCc;
    protected $toBcc;
    protected $toReply;
    protected $priority;
    protected $attachment;
    protected $emailAddressSystem;
    protected $isDelete;
    protected static $defaultMail;
    protected $recieverId;

    /**
     * override constructor
     */
    public function __construct(array $attributes = array()) {
        parent::__construct($attributes);
        $this->emailAddressSystem = CoreConfigData::getEmailAddress();
        if (!$this->from_email) {
            $this->from_email = $this->emailAddressSystem['email'];
            $this->from_name = $this->emailAddressSystem['name'];
        }

        $this->toCc = [];
        $this->toBcc = [];
        $this->toReply = [];
        $this->attachment = [];
        $this->priority = null;
        // Backup your default mailer
        self::$defaultMail = Mail::getSwiftMailer();
        $this->recieverId = [];
    }

    /**
     * verify account gmail
     *
     * @param string $email
     * @param string $password - app password
     * @return boolean
     */
    public function verifyMail($email, $password)
    {
        if (strlen($password) !== self::APP_PASS_LENGTH) {
            return false;
        }
        $mail = new PHPMailer();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = $email;
        $mail->Password = $password;
        try {
            return $mail->smtpConnect();
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * set from address
     */
    public function setFrom($email, $name = null)
    {
        $this->from_email = $email;
        $this->from_name = $name;
        return $this;
    }

    /**
     * set to address
     */
    public function setTo($email, $name = null)
    {
        $this->to_email = ViewCore::standardEmail($email);
        $this->to_name = $name;
        return $this;
    }

    /**
     * set subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * set template
     */
    public function setTemplate($templateName, $templateData = null)
    {
        $this->template_name = $templateName;
        if ($templateData) {
            $this->template_data = base64_encode(serialize($templateData));
        }
        return $this;
    }

    /**
     * set sent plan sent email
     */
    public function setSentPlan($sentPlan = null)
    {
        $this->sent_plan = $sentPlan;
        return $this;
    }

    /**
     * set delete item after send
     *
     * @return \self
     */
    public function setIsDelete()
    {
        $this->isDelete = true;
        return $this;
    }

    /**
     * rewrite save model
     */
    public function save(array $options = array(), $config = []) {
        try {
            if (!isset($config['not_set_option'])) {
                $this->setOption();
            }
            if (!$this->created_at) {
                $this->created_at = Carbon::now()->format('Y-m-d H:i:s');
            }

            try {
                //put notify
                if (isset($this->recieverId) && $this->recieverId) {
                    RkNotify::put(
                        $this->recieverId,
                        $this->noti_content ? $this->noti_content : $this->subject,
                        $this->link,
                        ['actor_id' => $this->actor_id, 'icon' => $this->noti_icon, 'category_id' => $this->category_id,
                            'content_detail' => $this->content_detail]
                    );
                    unset($this->recieverId);
                    unset($this->noti_content);
                    unset($this->link);
                    unset($this->actor_id);
                    unset($this->noti_icon);
                    unset($this->category_id);
                    unset($this->content_detail);
                }
            } catch (\Exception $ex) {
                \Log::info($ex);
            }

            parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    /**
     *  get data email queues
     */
    public static function getAllEmailQueues()
    {
        $emailQueue = self::getTableName();
        $pager = Config::getPagerData();
        $sortFilterPagerData = Config::getDirClass('id');
        if ($sortFilterPagerData) {
            $collectionModel = self::select("{$emailQueue}.id", "{$emailQueue}.from_email", "{$emailQueue}.to_email", "{$emailQueue}.subject", "{$emailQueue}.created_at", "{$emailQueue}.send_at", "{$emailQueue}.error")
            ->orderBy($pager['order'], $pager['dir']);
        } else {
            $collectionModel = self::select("{$emailQueue}.id", "{$emailQueue}.from_email", "{$emailQueue}.to_email", "{$emailQueue}.subject", "{$emailQueue}.created_at", "{$emailQueue}.send_at", "{$emailQueue}.error")
            ->orderBy("{$emailQueue}.id", "desc");
        }
        $collectionModel = self::filterGrid($collectionModel, [], null, 'LIKE');
        $collectionModel = self::pagerCollection($collectionModel, $pager['limit'], $pager['page']);
        return $collectionModel;
    }
    /**
     * Dùng trong bulk insert get giá trị của từng field trong bảng
     */
    public function getValue(array $options = array(), $config = []) {
        try {
            if (!isset($config['not_set_option'])) {
                $this->setOption();
            }
            if (!$this->created_at) {
                $this->created_at = Carbon::now()->format('Y-m-d H:i:s');
            }
            return $this->attributes;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get option
     */
    public function setOption()
    {
        $result = [];
        if ($this->toCc) {
            $result['cc'] = $this->toCc;
        }
        if ($this->toBcc) {
            $result['bcc'] = $this->toBcc;
        }
        if ($this->toReply) {
            $result['reply'] = $this->toReply;
        }
        if ($this->priority) {
            $result['priority'] = $this->priority;
        }
        if ($this->attachment) {
            $result['attachment'] = $this->attachment;
        }
        if ($this->isDelete) {
            $result['is_delete'] = $this->isDelete;
        }
        if ($result) {
            $this->option = serialize($result);
        }
        return $this;
    }

    /**
     * add cc mail
     */
    public function addCc($email, $name = null)
    {
        $this->toCc[] = [
            'email' => ViewCore::standardEmail($email),
            'name' => $name
        ];
        return $this;
    }

    /*
     * add cc email by list employees
     */
    public function addCcRelated($relateds)
    {
        if ($relateds && !$relateds->isEmpty()) {
            foreach ($relateds as $emp) {
                $this->addCc($emp->email, $emp->name)
                        ->addCcNotify($emp->id);
            }
        }
        return $this;
    }

    /**
     * add Bcc mail
     */
    public function addBcc($email, $name = null)
    {
        $this->toBcc[] = [
            'email' => ViewCore::standardEmail($email),
            'name' => $name
        ];
        return $this;
    }

    /**
     * add attachment file
     *
     * @param string $filePath full page of file
     * @param boolean $deleteAfterSend
     * @return \self
     */
    public function addAttachment($filePath, $deleteAfterSend = true)
    {
        $this->attachment[] = [
            'path' => $filePath,
            'delete' => $deleteAfterSend
        ];
        return $this;
    }

    /**
     * add cc mail
     */
    public function addReply($email, $name = null)
    {
        $this->toReply[] = [
            'email' => $email,
            'name' => $name
        ];
        return $this;
    }

    /**
     * add cc mail
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * set notify data
     */
    public function setNotify($recieverId, $content = null, $link = null, $data = [])
    {
        if (!is_array($recieverId)) {
            $recieverId = [$recieverId];
        }
        $this->recieverId = array_merge($this->recieverId, $recieverId);
        $this->noti_content = $content;
        $this->link = $link;
        $this->actor_id = isset($data['actor_id']) ? $data['actor_id'] :
            (auth()->id() ? auth()->id() : null);
        $this->noti_icon = isset($data['icon']) ? $data['icon'] : null;
        $this->category_id = isset($data['category_id']) ? $data['category_id'] : \Rikkei\Notify\Classes\RkNotify::CATEGORY_OTHER;
        $this->content_detail = isset($data['content_detail']) ? $data['content_detail'] : null;
        return $this;
    }

    /*
     * set cc notify
     */
    public function addCcNotify($toId = null)
    {
        if ($toId) {
            $this->recieverId[] = $toId;
        }
        return $this;
    }

    /**
     * get template data
     */
    public function getTemplateData()
    {
        if ($this->template_data) {
            if (ViewCore::isSerialized($this->template_data)) {
                return unserialize($this->template_data);
            }
            return unserialize(base64_decode($this->template_data));
        }
        return null;
    }

    /**
     * get option
     */
    public function getOption()
    {
        if ($this->option) {
            return unserialize($this->option);
        }
        return false;
    }

    /**
     * get cc email
     */
    public function getCc($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['cc']))  {
            return null;
        }
        return $option['cc'];
    }

    /**
     * get bcc email
     */
    public function getBcc($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['bcc']))  {
            return null;
        }
        return $option['bcc'];
    }

    /**
     * get reply email
     */
    public function getReply($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['reply']))  {
            return null;
        }
        return $option['reply'];
    }

    /**
     * get priority email
     */
    public function getPriority($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['priority']))  {
            return null;
        }
        return $option['priority'];
    }

    /**
     * get attachment file
     */
    public function getAttachment($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['attachment']))  {
            return null;
        }
        return $option['attachment'];
    }

    /**
     * get attachment file
     */
    public function getIsDelete($option = null)
    {
        if ($option === null) {
            $option = $this->getOption();
        }
        if (!isset($option['is_delete']))  {
            return null;
        }
        return $option['is_delete'];
    }

    /**
     * sent all email in queue
     */
    public static function sentAll($error = 0, $returnView = false)
    {
        self::createFolder();
        if (Storage::exists(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS)) {
            return true;
        }
        $collection = self::where(function($query) {
            $query->orWhere(function($query) {
                $query->whereNull('send_at')
                    ->whereNull('sent_plan');
            })->orWhere(function($query) {
                $query->whereNull('send_at')
                    ->where('sent_plan', '!=', null)
                    ->where('sent_plan', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            });
        });
        if ($error == 0) {
            $collection->whereNull('error');
        } else {
            $collection->where('error', '!=', null);
        }
        $collection = $collection->get();
        if (!count($collection)) {
            return true;
        }
        Storage::put(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS, 1,self::ACCESS_FILE);
        @chmod(storage_path('app/' . self::FOLDER_PROCESS . '/' . self::FILE_PROCESS),
            self::ACCESS_FOLDER);
        $i = 0;
        foreach ($collection as $item) {
            $i++;
            self::sentItem($item, $returnView);
            if ($i % 30 === 0) { // send 30 item, sleep 1min
                sleep(60);
            }
        }
        Storage::delete(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS);
    }

    /**
     * sent a mail in queue
     */
    public static function sentItem($item, $returnView = false)
    {
        $option = $item->getOption();
        $attachment = $item->getAttachment($option);
        if ($returnView) {
            echo view($item->template_name, ['data' => $item->getTemplateData()])
                ->render();
            return true;
        }
        try {
            //get mail config in table "core_config_datas" with key "cssmail"
            $mailPqa = CoreConfigData::getCssMail(2);
            if(count($mailPqa) > 1 && $mailPqa[1] == trim($item->from_email)) {
                self::setNewMailToSend($mailPqa);
            } elseif ($item->from_email == config('mail.special_date_mail')) {
                self::setMailFromConfig([
                    1 => $item->emailAddressSystem['name'],
                    2 => $item->from_email,
                    3 => config('mail.special_date_password')
                ]);
                $item->from_email = config('mail.special_date_mail');
                $item->from_name = $item->emailAddressSystem['name'];
            } else {
                // Gửi mail bằng mail hệ thống
                // Trong trường hợp cần reply to mail cá nhân thì set thêm attribute reply-to
                $item->from_email = $item->emailAddressSystem['email'];
                if (empty($item->from_name)) {
                    $item->from_name = $item->emailAddressSystem['name'];
                }
            }
            Mail::send($item->template_name, ['data' => $item->getTemplateData()]
                , function ($message) use ($item, $option, $attachment)
            {
                $message->from($item->from_email, $item->from_name);
                $message->to($item->to_email, $item->to_name);
                $message->subject(preg_replace('!\s+!', ' ', $item->subject)); //replace multiple spaces to single space

                $cc = $item->getCc($option);
                if ($cc) {
                    foreach ($cc as $ccItem) {
                        $message->cc($ccItem['email'], $ccItem['name']);
                    }
                }
                $bcc = $item->getBcc($option);
                if ($bcc) {
                    foreach ($bcc as $bccItem) {
                        $message->bcc($bccItem['email'], $bccItem['name']);
                    }
                }
                $reply = $item->getReply($option);
                if ($reply) {
                    foreach ($reply as $replyItem) {
                        $message->replyTo($replyItem['email'], $replyItem['name']);
                    }
                }
                $priority = $item->getPriority($option);
                if ($priority) {
                    $message->priority($priority);
                } else {
                    $message->priority(Swift_Mime_SimpleMessage::PRIORITY_NORMAL);
                }
                if ($attachment) {
                    foreach ($attachment as $file) {
                        if (isset($file['path']) &&
                            $file['path'] &&
                            file_exists($file['path'])
                        ) {
                            $message->attach($file['path']);
                        }
                    }
                }
            });
            if ($attachment) {
                foreach ($attachment as $file) {
                    if (isset($file['path']) &&
                        $file['path'] &&
                        file_exists($file['path'])
                    ) {
                        if (isset($file['delete']) && !$file['delete']) {
                        } else {
                            @unlink($file['path']);
                        }
                    }
                }
            }
            if ($item->getIsDelete($option)) {
                $item->delete();
            } else {
                $item->send_at = Carbon::now()->format('Y-m-d H:i:s');
                $item->save([], ['not_set_option' => 1]);
            }
        } catch (Exception $ex) {
            $item->error = $ex->getMessage();
            $item->save([], ['not_set_option' => 1]);
            Storage::delete(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS);
            Mail::setSwiftMailer(self::$defaultMail);
            throw $ex;
        } finally {
            Mail::setSwiftMailer(self::$defaultMail);
        }
    }

    protected function bodyFuncSend($message, $item, $option, $attachment = [])
    {
        $message->from($item->from_email, $item->from_name);
        $message->to($item->to_email, $item->to_name);
        $message->subject($item->subject);

        $cc = $item->getCc($option);
        if ($cc) {
            foreach ($cc as $ccItem) {
                $message->cc($ccItem['email'], $ccItem['name']);
            }
        }
        $bcc = $item->getBcc($option);
        if ($bcc) {
            foreach ($bcc as $bccItem) {
                $message->bcc($bccItem['email'], $bccItem['name']);
            }
        }
        $reply = $item->getReply($option);
        if ($reply) {
            foreach ($reply as $replyItem) {
                $message->replyTo($replyItem['email'], $replyItem['name']);
            }
        }
        $priority = $item->getPriority($option);
        if ($priority) {
            $message->priority($priority);
        } else {
            $message->priority(Swift_Mime_SimpleMessage::PRIORITY_NORMAL);
        }
        if ($attachment) {
            foreach ($attachment as $file) {
                if (isset($file['path']) &&
                    $file['path'] &&
                    file_exists($file['path'])
                ) {
                    $message->attach($file['path']);
                }
            }
        }
    }

    /**
     * sent now a mail
     *
     * @param array $dataNow
     *      [
     *          'html' => $rawHtmlString
     *      ]
     */
    public function sentNow(array $dataNow = [])
    {
        $this->setOption();
        $option = $this->getOption();
        $attachment = $this->getAttachment($option);
        $item = $this;
        if (isset($dataNow['html'])) { // body html
            Mail::send([], [], function ($message)
                use ($item, $option, $attachment, $dataNow)
            {
                $this->bodyFuncSend($message, $item, $option, $attachment);
                $message->setBody($dataNow['html'], 'text/html');
            });
        } else { // body view
            Mail::send($this->template_name, ['data' => $this->getTemplateData()]
                , function ($message) use ($item, $option, $attachment)
            {
                $this->bodyFuncSend($message, $item, $option, $attachment);
            });
        }
        if ($attachment) {
            foreach ($attachment as $file) {
                if (isset($file['path']) &&
                    $file['path'] &&
                    file_exists($file['path'])
                ) {
                    if (isset($file['delete']) && !$file['delete']) {
                    } else {
                        @unlink($file['path']);
                    }
                }
            }
        }
    }

    /**
     * Set new mail address to send
     * @param array $mailPqa variable config in table core_config_datas key cssmail
     */
    protected static function setNewMailToSend($mailPqa) {
        // Setup your gmail mailer
        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls');
        $transport->setUsername($mailPqa[1]);
        $transport->setPassword($mailPqa[2]);
        // Any other mailer configuration stuff needed...

        $gmail = new Swift_Mailer($transport);

        // Set the mailer as gmail
        Mail::setSwiftMailer($gmail);
    }

    /**
     * Set new mail with another email
     * @param array $emailConfig variable config in table core_config_datas key cssmail
     */
    protected static function setMailFromConfig($emailConfig) {
        // Setup your gmail mailer
        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls');
        $transport->setUsername($emailConfig[2]);
        $transport->setPassword($emailConfig[3]);
        // Any other mailer configuration stuff needed...
        $gmail = new Swift_Mailer($transport);
        // Set the mailer as gmail
        Mail::setSwiftMailer($gmail);
    }


    /**
     * create folder
     */
    protected static function createFolder()
    {
        if (!Storage::exists(self::FOLDER_PROCESS)) {
            Storage::makeDirectory(self::FOLDER_PROCESS, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_PROCESS), self::ACCESS_FOLDER);
    }

    /**
     * check email queue process
     *
     * @return boolean
     */
    public static function checkProcessing()
    {
        if (Storage::exists(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS)) {
            return true;
        }
        return false;
    }

    /**
     * delete processing
     */
    public static function deleteProcessing()
    {
        Storage::delete(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS);
    }

    /**
     * get all layout of email
     *
     * @return array
     */
    public static function layouts()
    {
        return [
            0 => [
                'template' => 'layouts.email',
                'label' => 'Default'
            ],
            1 => [
                'template' => 'core::emails.1_new_year',
                'label' => 'New year'
            ],
            /*2 => [
                'template' => 'core::emails.2_christmas',
                'label' => 'Christmas'
            ],*/
            3 => [
                'template' => 'core::emails.3_birthday_company',
                'label' => 'Birthday company'
            ],
            4 => [
                'template' => 'core::emails.4_summer',
                'label' => 'Summer'
            ],
            5 => [
                'template' => 'core::emails.5_christmas',
                'label' => 'Christmas'
            ],
            6 => [
                'template' => 'core::emails.6_new_year',
                'label' => 'New year 2018'
            ],
            7 => [
                'template' => 'core::emails.7_eventday_company',
                'label' => 'Event day'
            ],
            8 => [
                'template' => 'core::emails.8_birthday_candidate',
                'label' => 'Birthday candidate'
            ],
        ];
    }

    /**
     * get layout of a email
     *
     * @param array $dataTemplate
     * @return string
     */
    public static function getLayout($dataTemplate)
    {
        $layouts = self::layouts();
        if (!isset($dataTemplate['layout_email']) ||
            !$dataTemplate['layout_email']
        ) {
            return $layouts[0]['template'];
        }
        if (array_key_exists($dataTemplate['layout_email'], $layouts)) {
            return $layouts[$dataTemplate['layout_email']]['template'];
        }
        return $layouts[0]['template'];
    }

    /**
     * get layout from config
     *
     * @return string
     */
    public static function getLayoutConfig($emailLayoutValue = null)
    {
        $layouts = self::layouts();
        if ($emailLayoutValue === null) {
            $emailLayoutValue = CoreConfigData::getEmailLayout();
        }
        if (!isset($layouts[$emailLayoutValue])) {
            return $layouts[0]['template'];
        }
        return $layouts[$emailLayoutValue]['template'];
    }
}
