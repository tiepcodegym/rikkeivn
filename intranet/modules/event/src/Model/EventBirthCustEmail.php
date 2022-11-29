<?php

namespace Rikkei\Event\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;

class EventBirthCustEmail extends CoreModel
{
    use SoftDeletes;
    protected $table = 'event_birth_email_cust';
    protected $fillable = ['sale_email', 'email', 'status', 'email_sender', 'is_sending'];

    const STATUS_NO = 0;
    const STATUS_YES = 1;

    const NOT_SENDING = 0;
    const IS_SENDING = 1;

    /**
     * get option status
     * 
     * @return array
     */
    public static function toOptionStatus()
    {
        return [
            self::STATUS_NO => 'Chưa gửi',
            self::STATUS_YES => 'Đã gửi',
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
            $status = self::toOptionStatus();
        }
        return View::getLabelOfOptions($this->status, $status);
    }

    public static function getListBy()
    {
        $tblMailCust = self::getTableName();
        $userCurrent = Permission::getInstance()->getEmployee();

        $collection = self::select(
            "{$tblMailCust}.*"
        )
        ->where(function($query) use ($userCurrent) {
            $query->where('sale_email', $userCurrent->email)
                ->orWhere('email_sender', $userCurrent->email);
        });
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $pager = Config::getPagerData(null, ['dir' => 'DESC']);
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

}
