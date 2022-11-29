<?php

namespace Rikkei\Event\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Employee;
use Exception;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\View\OptionCore;

class EventBirthday extends CoreModel
{

    protected $table = 'event_birth_cust';
    public $timestamps = false;

    const STATUS_PENDING = 0;
    const STATUS_YES = 1;
    const STATUS_NO = 2;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    const BOOKING_SELF = 0;
    const BOOKING_RK = 1;

    const TOUR_NO = 1;
    const TOUR_HANOI = 'Hanoi';
    const TOUR_DANANG = 'Danang';
    const TOUR_GOLF = 'golf';
    const TOUR_DU_THUYEN = 'du_thuyen';
    
    const SHOW_TOUR = 1;
    const SHOW_NOT_TOUR = 2;

    use SoftDeletes;

    /**
     * get option gender
     * 
     * @return array
     */
    public static function toOptionGender()
    {
        return [
            self::GENDER_MALE => Lang::get('event::view.Male'),
            self::GENDER_FEMALE => Lang::get('event::view.Female'),
        ];
    }

    /**
     * find item follow email
     * 
     * @param string $email
     * @return object
     */
    public static function findItemFollowEmail($email)
    {
        $item = self::where('email', $email)
                ->first();
        if ($item) {
            return $item;
        }
        $item = new self;
        $item->email = $email;
        return $item;
    }

    /**
     * find item follow email
     * 
     * @param string $token
     * @return object
     */
    public static function findItemFollowToken($token, $item = null)
    {
        $result = self::where('token', $token);
        if ($item) {
            $result->where('id', '<>', $item->id);
        }
        return $result->first();
    }

    /**
     * get collection to show grid data
     */
    public static function getGridData($hasPaginate = true)
    {
        $pager = Config::getPagerData();
        $collection = self::select('id', 'name', 'email', 'gender', 'company', 'address', 'phone', 'note', 'status', 'sender_name', 'sender_email', 'updated_at', 'attacher', 'booking_room', 'join_tour', 'email_register', 'show_tour', 'customer_type');
        if (\Rikkei\Core\View\Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        $collection->whereNull('deleted_at');
        self::filterGrid($collection);
        if ($hasPaginate) {
            self::pagerCollection($collection, $pager['limit'], $pager['page']);
        } else {
            $collection = $collection->get();
        }
        return $collection;
    }

    /**
     * get label status
     * 
     * @return array
     */
    public static function toOptionLabelStatus()
    {
        return [
            self::STATUS_PENDING => Lang::get('event::view.Not confirm'),
            self::STATUS_YES => Lang::get('event::view.Attend'),
            self::STATUS_NO => Lang::get('event::view.Refuse')
        ];
    }

    /**
     * get status
     * 
     * @param array $status
     */
    public function getStatus($status = null)
    {
        if (!$status) {
            $status = self::toOptionLabelStatus();
        }
        return View::getLabelOfOptions($this->status, $status);
    }

    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array())
    {
        try {
            $this->updated_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get option gender
     * 
     * @return array
     */
    public static function toOptionLang()
    {
        return [
            'ja' => Lang::get('event::view.Japanese'),
            'en' => Lang::get('event::view.English'),
            //'vi' => Lang::get('event::view.Vietnamese'),
        ];
    }

    public function getAppPass()
    {
        $emp = Employee::getEmpByEmail($this->sender_email);
        return isset($emp->app_password) ? $emp->app_password : '';
    }

    public static function crontabAlarmConfirmEventDay()
    {
        Log::info('start crontabAlarmConfirmEventDay');
        $events = self::where('status', self::STATUS_PENDING)->get();
        if (!$events) {
            return;
        }
        Log::info("Total row:" . count($events));

        foreach ($events as $event) {
            $emailQueue = new EmailQueue();
            $emailQueue->setSubject('Hệ thống thử nghiệp:  Chưa cấu hình [' . __FILE__ . ' - online:' . __LINE__ . ']');
            $emailQueue->setFrom($event->sender_email, $event->sender_name);
            $emailQueue->setTemplate('event::eventday.email.alarmConfirm', $event->toArray());
            $emailQueue->setTo($event->email);
            $emailQueue->save();
        }
        Log::info('End crontabAlarmConfirmEventDay');
    }

    public static function crontabAlarmEventDay()
    {
        Log::info('start crontabAlarmEventDay');
        $events = self::where('status', self::STATUS_YES)->get();
        if (!$events) {
            return;
        }
        Log::info("Total row:" . count($events));
        foreach ($events as $event) {
            $emailQueue = new EmailQueue();
            $emailQueue->setSubject('Hệ thống thử nghiệp: Chưa cấu hình [' . __FILE__ . ' - online:' . __LINE__ . ']');
            $emailQueue->setFrom($event->sender_email, $event->sender_name);
            $emailQueue->setTemplate('event::eventday.email.alarmEventDay', $event->toArray());
            $emailQueue->setTo($event->email);
            $emailQueue->save();
        }
        Log::info('End crontabAlarmEventDay');
    }

    /**
     * to option booking room and fighter
     * 
     * @return array
     */
    public static function toOptionBooking()
    {
        return [
            self::BOOKING_SELF => 'Không',
            self::BOOKING_RK => 'Có',
        ];
    }

    /**
     * to option join tour
     * 
     * @return array
     */
    public static function toOptionJoinTour()
    {
        return [
            self::TOUR_NO => Lang::get('event::view.No'),
            self::TOUR_GOLF => 'Golf',
            self::TOUR_DU_THUYEN => 'Du thuyền',
        ];
    }

    /**
     * get label booking room of item
     * 
     * @return string
     */
    public function getBookingRoom()
    {
        if (!$this->booking_room) {
            return null;
        }
        return View::getLabelOfOptions($this->booking_room, self::toOptionBooking());
    }

    /**
     * get label booking room of item
     * 
     * @return string
     */
    public function getJoinTour()
    {
        if (!$this->join_tour) {
            return null;
        }
        return View::getLabelOfOptions($this->join_tour, self::toOptionJoinTour());
    }

    /**
     * get label booking room of item
     * 
     * @return string
     */
    public function getShowTour()
    {
        if (!$this->show_tour) {
            return null;
        }
        return View::getLabelOfOptions($this->show_tour, OptionCore::yesNo());
    }
}
