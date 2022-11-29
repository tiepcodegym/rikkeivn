<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;
use Lang;

class LeaveDayHistories extends CoreModel
{
    public $incrementing = false;

    protected $table = 'leave_day_histories';

    const TYPE_EDIT = 1;
    const TYPE_IMPORT = 2;
    const TYPE_OT = 3;
    const TYPE_AUTO = 4;
    const TYPE_APPROVE = 5;
    const TYPE_CANCEL_APPROVE = 6;
    const TYPE_VIETNAM_JAPAN = 7;

    /**
     * Override construct
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->id = $this->setAttrId();
    }

    /**
     * Type of primary key in this table is text, not increament
     * So each time insert a record, must set the value for primary key
     *
     * @return string
     */
    private function setAttrId()
    {
        return md5(strtotime(Carbon::now()) . $this->generateRandomString());
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * List histories
     *
     * @return collection self
     */
    public function histories()
    {
        $pager = Config::getPagerData();
        $result = self::select([
                'leave_day_histories.*',
                'employees.employee_code',
                'employees.name',
                'owner.email as owner_email',
            ])
            ->leftJoin('employees as owner', 'owner.id', '=', 'leave_day_histories.created_by')
            ->join('employees', 'employees.id', '=', 'leave_day_histories.employee_id')
            ->orderBy('leave_day_histories.created_at', 'desc');
        $result = CoreModel::filterGrid($result);
        return self::pagerCollection($result, $pager['limit'], $pager['page']);
    }

    /**
     * Get history detail
     *
     * @param string $id
     *
     * @return LeaveDayHistories
     */
    public function historyInfo($id)
    {
        return self::leftJoin('employees as owner', 'owner.id', '=', 'leave_day_histories.created_by')
            ->join('employees', 'employees.id', '=', 'leave_day_histories.employee_id')
            ->select('leave_day_histories.*', 'employees.employee_code', 'employees.name', 'owner.email as owner_email')
            ->where('leave_day_histories.id', $id)
            ->first();
    }

    /**
     * Get label of type
     *
     * @return string
     */
    public function getType()
    {
        switch ($this->type) {
            case self::TYPE_EDIT:
                return Lang::get('manage_time::view.Edit');
            case self::TYPE_IMPORT:
                return Lang::get('manage_time::view.Import');
            case self::TYPE_OT:
                return Lang::get('manage_time::view.Ot');
            case self::TYPE_AUTO:
                return Lang::get('manage_time::view.Cron');
            case self::TYPE_APPROVE:
                return Lang::get('manage_time::view.Approve take day off');
            case self::TYPE_VIETNAM_JAPAN:
                return Lang::get('manage_time::view.Team vietnam change team japan');
            default:
                return Lang::get('manage_time::view.Cancel approve take day off');
        }
    }

    /**
     * Get content of history
     *
     * @return string
     */
    public function getContent()
    {
        $content = json_decode($this->content, true);
        if (!is_array($content)) {
            return '';
        }
        $contentHtml = '';
        $selfModel = new LeaveDayHistories();
        $contentHtml .= '<ul class="padding-left-20">';
        foreach ($content as $field => $value) {
            $contentHtml .= '<li>' . $selfModel->aliasField($field) . ': ' . $value['old'] . ' => ' . $value['new'] . '</li>';
            
        }
        $contentHtml .= '</ul>';
        return $contentHtml;
    }

    /**
     * Change field to title
     *
     * @param string $field     field of table leave_days
     *
     * @return string
     */
    public function aliasField($field)
    {
        switch ($field) {
            case 'day_last_year':
                return Lang::get('manage_time::view.Number day last year');
            case 'day_last_transfer':
                return Lang::get('manage_time::view.Number day last year use');
            case 'day_current_year':
                return Lang::get('manage_time::view.Number day current year');
            case 'day_seniority':
                return Lang::get('manage_time::view.Number day seniority');
            case 'day_ot':
                return Lang::get('manage_time::view.Number day OT');
            case 'day_vietnam_japan':
                return Lang::get('manage_time::view.Number day Vietnam Japan');
            case 'day_used':
            default:
                return Lang::get('manage_time::view.Number day used');
        }
    }

    /**
     * List types label
     *
     * @return array
     */
    public static function typesLabel()
    {
        return [
            self::TYPE_EDIT => Lang::get('manage_time::view.Edit'),
            self::TYPE_IMPORT => Lang::get('manage_time::view.Import'),
            self::TYPE_OT => Lang::get('manage_time::view.Ot'),
            self::TYPE_AUTO => Lang::get('manage_time::view.Cron'),
            self::TYPE_VIETNAM_JAPAN => Lang::get('manage_time::view.Vietnam Japan'),
        ];
    }
}
