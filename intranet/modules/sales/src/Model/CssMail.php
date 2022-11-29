<?php

namespace Rikkei\Sales\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use DB;
use Rikkei\Sales\Model\Css;
use Mail;
use Rikkei\Team\Model\Employee;
use Route;
use Illuminate\Support\Facades\Lang;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Project\Model\Project;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;

class CssMail extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'css_mail';
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sender', 'css_id', 'mail_to', 'resend', 'resend_date', 'code', 'name', 'gender'];
    
    const WEEK = '-2 week';
    const UNRESEND = 0;
    const NOT_MAKE = 0;
    const MAIL_ERROR = 0; //send mail error
    const MAIL_SUCCESS = 1; //send mail succes
    const SEND_MAIL_AT = '8:00'; //Css email notify send at
    
    const GENDER_FEMALE = 0;
    const GENDER_MALE = 1;

    public static function getTime() {
        $now = Carbon::now();
        $now->modify(self::WEEK);
        return $now->format('Y-m-d H:i:s');
    }

    public static function getDateNow() {
        $now = Carbon::now();
        return $now->format('Y-m-d');
    }

    /*
     * get customer by id
     * @param array $data 
     * return array
     */
    public static function saveData($data) {
        foreach ($data as $input) {
            $model = self::getCssMail($input['css_id'], $input['mail_to']); 
            $model = new CssMail();
            // if (!count($model)) {
            //     $model = new CssMail();
            // }
    
            $model->fill($input);
            $model->save();
        }

    }
    
    /**
     * check css that customer not made.
     * @param cssMail $css
     * @return array
     */
    public static function checkMade($css) {
        $result = [];
        foreach ($css as $item) {
            $codeResults = CssResult::getCodeResult($item->css_id);
            $cssMail = self::getCssMailByCssId($item->css_id);
            $check = false;
            foreach ($cssMail as $cm) {
                if (in_array($cm->code, $codeResults) || $cm->resend != self::UNRESEND) {
                    $check = true;
                }
            }
            if (!$check) {
                $result[] = $item;
            }
        }

        return $result; 
    }

    /**
     * Get all Css all customer not made or unresend mail to customer dont make
     * @return type
     */
    public static function getCss() {
        $sql = "select tmp_table_1.* from 
            (select *, count(*) as css_mail_count from css_mail where css_mail.deleted_at is null group by css_id) 
                as tmp_table_1
        left join
        (select css_id, count(Distinct code) as css_result_count 
	from css_result group by css_id) 
                as tmp_table_2
        on tmp_table_1.css_id = tmp_table_2.css_id
        where (tmp_table_2.css_result_count is null or 
                (tmp_table_1.css_mail_count > tmp_table_2.css_result_count 
                        and tmp_table_1.css_mail_count is not null) ) 
                        and tmp_table_1.created_at <= ? 
        group by tmp_table_1.css_id";
        
        $css = DB::select($sql, [self::getTime()]);
        return self::checkMade($css);
    }
    
    /**
     * Get all Css all customer not made or unresend mail to customer dont make by sender
     * @param int $sender employee_id
     * @return type
     */
    public static function getCssBySender($sender) {
        $sql = "select tmp_table_1.* from 
            (select *, count(*) as css_mail_count from css_mail group by css_id) 
                as tmp_table_1
        left join
        (select css_id, count(Distinct code) as css_result_count 
	from css_result group by css_id) 
                as tmp_table_2
        on tmp_table_1.css_id = tmp_table_2.css_id
        where (tmp_table_2.css_result_count is null or 
                (tmp_table_1.css_mail_count > tmp_table_2.css_result_count 
                        and tmp_table_1.css_mail_count is not null) ) 
                        and tmp_table_1.created_at <= ? 
                        and sender = ?
        group by tmp_table_1.css_id";
        
        $css = DB::select($sql, [self::getTime(),  $sender]);
        return self::checkMade($css);
    }
    
    /**
     * Get CssMail not make
     * @param int $sender
     * @return type
     */
    public static function getCssNotMakeBySender($sender) {
        return DB::select('select css_id from css_mail '
                . 'where css_id not in (select css_id from css_result group by css_id) '
                . ' and created_at <= ?'
                . ' and resend = ?'
                . ' and sender = ?'
                . ' group by css_id', [self::getTime(), self::UNRESEND, $sender]);
    }
    
    public static function getCssMailByCssIdSender($cssId, $sender) {
        return DB::select('select * from css_mail '
                . 'where css_id = ? '
                . ' and sender = ?', [$cssId, $sender]);
    }

    public static function getLastSendCssMailByCssId($cssId) {
        $collection = DB::select('select max(`id`) as id from css_mail where css_id = '.$cssId.' and deleted_at is null group by mail_to');
        $ids = [];
        if ($collection) {
            foreach ($collection as $key => $item) {
                $ids[] = $item->id;
            }
        }
        if ($ids) {
            return self::whereIn('id', $ids)->get();
        }
        return null;
    }

    /**
     * Get CssMail by css_id and email
     * @param int $cssId
     * @param string $email
     * @return CssMail
     */
    public static function getCssMail($cssId, $email) {
        return self::where([['css_id', $cssId] , ['mail_to', $email]])
                ->select('*')
                ->first();
    }

    /**
     * Get CssMail by css_id and list email
     * @param int $cssId
     * @param array $email
     * @return CssMail
     */
    public static function getListCssMail($cssId, $email)
    {
        return self::where('css_id', $cssId)
                    ->whereIn('mail_to', $email)
                    ->select('*')
                    ->get();
    }

    /**
     * Get CssMail by css_id 
     * @param int $cssId
     * @return CssMail
     */
    public static function getCssMailByCssId($cssId) {
        return self::where('css_id', $cssId)
                ->select('*')
                ->get();
    }
   
    /** 
    * Send mail to PQA khi customer don't make CSS
    */
    public static function sendMail() { 
        //Get Css not make
        $cssMails = self::getCss(); 
        $senders = [];
        foreach ($cssMails as $cssMail) {
            if (!in_array($cssMail->sender, $senders)) {
                $senders[] = $cssMail->sender;
            }
        } 

        $toNotifyIds = [];
        if (count($senders)) {
            foreach ($senders as $sender) { 
                $notMake = self::getCssBySender($sender);
                $emp = Employee::getEmpById($sender);
                if (!$emp) {
                    continue;
                }
                $mailTo = $emp->email;
                $toNotifyIds[] = $emp->id;
                $data['emp'] = $emp;
                if (count($notMake)) {
                    $i = 1;
                    $data['cssMail'] = [];
                    foreach ($notMake as $item) {
                        if(self::getCssIdClosed($item->css_id)) {
                            $css = Css::find($item->css_id);
                            if (!$css) {
                                continue;
                            }
                            $cssMails = self::getCssMailByCssId($item->css_id);
                            $makeCodes = CssResult::getCode($item->css_id);
                            $emails = [];
                            $codes = [];
                            $notMakePerson = [];
                            if (count($makeCodes)) {
                                foreach ($makeCodes as $value) {
                                    $codes[] = $value->code;
                                }
                            }
                            foreach ($cssMails as $cssMail) {
                                if (!in_array($cssMail->code, $codes)) {
                                    $notMakePerson[] = [
                                        'name' => $cssMail->name,
                                        'email' => $cssMail->mail_to
                                    ];
                                }
                            }
                            
                            $data['cssMail'][] = [
                                'url' => url("/css/preview/".$css->token."/".$css->id),
                                'cssId' => $item->css_id,
                                'no' => $i,
                                'notMakePerson' => $notMakePerson,
                            ];
                            $i++;
                        }
                    }
                }
               
                //Send mail 
                if (isset($data['cssMail']) && $data['cssMail']) {
                    Mail::send('sales::css.pqaMail', $data, function ($message) use($mailTo) {
                        $message->from('intranet@rikkeisoft.com', 'Rikkeisoft');
                        $message->to($mailTo)
                                ->subject(Lang::get('sales::message.Mail pqa title'));
                    });
                }
            }
        }
        //set notify
        if ($toNotifyIds) {
            \RkNotify::put(
                $toNotifyIds,
                Lang::get('sales::message.Mail pqa title'),
                route('sales::css.list'),
                ['schedule_code' => 'sale_css_not_made', 'icon' => 'customer.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }
    }

    public static function sendMail2() {
        $month1 = Carbon::now()->subMonth(1)->format('Y-m-d');
        $month2 = Carbon::now()->subMonth(2)->format('Y-m-d');
        $cssMails = CssMail::whereDate('created_at', '>=', $month2)->whereDate('created_at', '<=', $month1)
            ->get()->groupBy('sender');

        $toNotifyIds = [];
        if (count($cssMails)) {
            foreach ($cssMails as $key => $itemCssMail) {
                $emp = Employee::getEmpById($key);
                if (!$emp) {
                    continue;
                }
                $mailTo = $emp->email;
                $data['emp'] = $emp;

                if (count($itemCssMail)) {
                    $i = 1;
                    $data['cssMail'] = [];
                    foreach ($itemCssMail as $key2 => $css_mail) {
                        $cssResult = CssResult::where('css_id', $css_mail->css_id)->where('code', $css_mail->code)->first();
                        if (!$cssResult) {
                            if(self::getCssIdClosed($css_mail->css_id)) {
                                $css = Css::find($css_mail->css_id);
                                if (!$css || $css->status == Css::STATUS_CANCEL) {
                                    continue;
                                }
                                if (!in_array($emp->id, $toNotifyIds)) {
                                    $toNotifyIds[] = $emp->id;
                                }
                                $person = [
                                    'name' => $css_mail->name,
                                    'email' => $css_mail->mail_to
                                ];

                                if (isset($data['cssMail'][$css_mail->css_id])) {
                                    $data['cssMail'][$css_mail->css_id]['notMakePerson'][] = $person;
                                } else {
                                    $data['cssMail'][$css_mail->css_id] = [
                                        'url' => url("/css/preview/".$css->token."/".$css->id),
                                        'cssId' => $css_mail->css_id,
                                        'no' => $i,
                                        'notMakePerson' => [
                                            $person
                                        ]
                                    ];
                                    $i++;
                                }
                            }
                        }
                    }
                }

                //Send mail 
                if (isset($data['cssMail']) && $data['cssMail']) {
                    $subject = Lang::get('sales::message.Mail pqa title');
                    $template = 'sales::css.pqaMail';
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($mailTo)
                            ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft')
                            ->setSubject($subject)
                            ->setTemplate($template, $data);
                    $emailQueue->save();
                }
            }
        }

        //set notify
        if ($toNotifyIds) {
            \RkNotify::put(
                $toNotifyIds,
                Lang::get('sales::message.Mail pqa title'),
                route('sales::css.list'),
                ['schedule_code' => 'sale_css_not_made', 'icon' => 'customer.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }
    }
    
    /**
     * check css for project is closed
     */
    public static function getCssIdClosed($cssId) {
        $check = Css::where('css.id',$cssId)
            ->leftJoin('projs','css.projs_id','=','projs.id')
            ->whereNotIn('projs.state',[Project::STATE_CLOSED])->first();
        if ($check) {
            return true;
        }
        return false;
    }

    /**
     * get label gender of customer lang EN send mail css.
     */
    public static function getGenderCustomer($lang = 'en')
    {
        if ($lang == 'en') {
            return [
                self::GENDER_FEMALE => 'Ms.',
                self::GENDER_MALE => 'Mr.',
            ];
        }
        if ($lang == 'vi') {
            return [
                self::GENDER_FEMALE => 'bà',
                self::GENDER_MALE => 'ông',
            ];
        }
        return [];
    }
}
