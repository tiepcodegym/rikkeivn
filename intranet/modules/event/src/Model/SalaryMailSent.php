<?php

namespace Rikkei\Event\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;

class SalaryMailSent extends CoreModel
{
    protected $table = 'salary_mail_sent';
    protected $fillable = ['file_id', 'employee_code', 'email', 'fullname', 'number_sent', 'sent_at'];

    /*
     * increment number sent email
     */
    public static function incrementNumberSent($fileId, $email)
    {
        $item = self::where('file_id', $fileId)
                ->where('email', $email)
                ->first();
        if (!$item) {
            return false;
        }
        $numSent = $item->number_sent + 1;
        $item->update([
            'number_sent' => $numSent,
            'sent_at' => \Carbon\Carbon::now()->toDateTimeString()
        ]);
        return $item;
    }

    /**
     * get detail sent mail
     * @param type $fileId
     */
    public static function getData($fileId)
    {
        $pager = Config::getPagerData();
        $collection = self::select()
                ->from(self::getTableName() . ' as sms')
                ->where('file_id', $fileId);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
