<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\Session;

class LeaveDayReason extends CoreModel
{
    use SoftDeletes;
    
    protected $table = 'leave_day_reasons';

    // Leave Day Reason special type
    const NORMAL_TYPE = 0;
    const SPECIAL_TYPE = 1;
    const BASIC_TYPE = 2;   //hưởng lương cơ bản

    const TEAM_TYPE_JP = 1;
    const TEAM_TYPE_VN = 2;
    const OBON = 'obon';
    const REASON_RELATIONSHIP_MEMBER_IS_DIE = 15;
    const REASON_RELATIONSHIP_MEMBER_IS_DIE_JA = 6;

    //Leave Code
    const CODE_MATERNITY = 'MATERNITY'; //Nghỉ thai sản
    const CODE_UNPAID_LEAVE = 'UNPAID_LEAVE'; //Nghỉ không phép

    const REASON_PAID_LEAVE_JA = 2; // Nghỉ có phép

    public static function getLeaveDayReasons($teamCodePre = null, $orderBy = 'sort_order', $dir = 'asc')
    {
    	$list = self::select([
            'leave_day_reasons.id',
            'name as reason_name',
            'name_en as reason_name_en',
            'name_ja as reason_name_jp',
            'sort_order',
            'salary_rate',
            'used_leave_day',
            'type',
            'repeated',
            'unit',
            'value',
            'team_type',
            'calculate_full_day',
        ])->orderBy($orderBy, $dir);

    	if (!empty($teamCodePre)) {
            if ($teamCodePre !== Team::CODE_PREFIX_JP) {
                $list->where('team_type', '=', self::TEAM_TYPE_VN);
            } else {
                $list->where('team_type', '=', self::TEAM_TYPE_JP);
            }
        }

        return $list->get();
    }

    /**
     * Get reason list for edit form
     *
     * @param int $regId
     * @param string $teamCodePre
     *
     * @return LeaveDayReason collection
     */
    public static function getListReasonsEditForm($regId, $teamCodePre = 'hanoi')
    {
        $list = self::select([
            'leave_day_reasons.id',
            'name as reason_name',
            'sort_order',
            'salary_rate',
            'used_leave_day',
            'type',
            'repeated',
            'unit',
            'value',
            'team_type',
            'calculate_full_day'
        ])->orderBy('sort_order', 'asc');
        if ($teamCodePre !== Team::CODE_PREFIX_JP) {
            $list->leftJoin('leave_day_registers', 'leave_day_registers.reason_id', '=', 'leave_day_reasons.id');
            $list->where(function ($query) use ($regId) {
                $query->where('type', '<>', self::SPECIAL_TYPE);
                $query->orWhere('leave_day_registers.id', $regId);
            });
            $list->groupBy('leave_day_reasons.id');
        }
        return $list->get();
    }

    public static function languageLeaveDayReasons($item, $lang)
    {
        if (isset($item)) {
            switch ($lang) {
                case 'vi':
                    return $item->reason_name;
                    break;
                case 'en':
                    return $item->reason_name_en;
                    break;
                case 'jp':
                    return $item->reason_name_jp;
                    break;
                default:
                    return $item->reason_name;
            }
        }
        return false;
    }

    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('id', 'name', 'salary_rate', 'sort_order', 'used_leave_day', 'updated_at', 'deleted_at');
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('sort_order', 'ASC');
        }

        self::filterGrid($collection,[],null,'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function countReasonbyName($reasonName, $reasonId = null)
    {
        $countReason = self::where('name', '=', $reasonName);
        if($reasonId){
            $countReason = $countReason->where('id', '!=', $reasonId);
        }

        return $countReason->count();
    }

    /**
     * Validate special type of leave day reason
     *
     * @param Request $request
     * @param $empId employee_id of leave registration form
     *
     * @return arrya key 'valid' => true: valid
     */
    public function isValidSpecialType($request, $empId)
    {
        if ($this->type != self::SPECIAL_TYPE) {
            return [
                'valid' => true,
            ];
        }

        if ($request->number_validate > $this->value) {
            return [
                'valid' => false,
                'message' => Lang::get('manage_time::message.The number day is greater than the number day allowed'),
            ];
        }

        if ($this->repeated
                && $this->countByUnit($empId, $request->reason, $request->start_date, $this->repeated, $this->unit, $request->register_id)) {
            return [
                'valid' => false,
                'message' => Lang::get('manage_time::message.With the application type :name, you can only apply for one off in :repeated :unit', [
                    'name' => $this->name,
                    'repeated' => $this->repeated,
                    'unit' => Lang::get('manage_time::view.' . $this->unit),
                ]),
            ];
        }

        return [
            'valid' => true,
        ];
    }

    /**
     * Count leave day applications by reason in a time period (before and after)
     *
     * @param int $empId employee_id of leave registration form
     * @param int $reasonId primary id of table leave_day_reasons
     * @param date|string $date
     * @param int $repeated number repeated of $unit
     * @param string $unit day, week, month, year
     * @param int|null $registerId id of leave registration form
     *
     * @return int
     */
    public function countByUnit($empId, $reasonId, $date, $repeated, $unit, $registerId = null)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        $count =  LeaveDayRegister::where(function ($query) use ($date, $repeated, $unit) {
                    $query->whereRaw("date(date_start) between date(date_add(date(date_sub(?, INTERVAL ? {$unit})), INTERVAL 1 DAY)) and date(?)", [
                        $date, $repeated, $date
                    ]);
                    $query->orWhereRaw("date(date_start) between date(?) and date(date_sub(date(date_add(?, INTERVAL ? {$unit})), INTERVAL 1 DAY)) ", [
                        $date, $date, $repeated
                    ]);
                })
                ->where('creator_id', $empId)
                ->where('reason_id', $reasonId)
                ->whereIn('status', [LeaveDayRegister::STATUS_UNAPPROVE, LeaveDayRegister::STATUS_APPROVED]);

        // Check case edit
        if ($registerId) {
            $count->where('id', '<>', $registerId);
        }

        return $count->count();
    }

    /**
     * get array text leave reason only 1 time
     * @return array
     */
    public function getArrLeaveReasonOnlyOnetime()
    {
        return [
            'Nghỉ cưới',
        ];
    }

    /**
     * get array text leave reason only 1 time month
     * @return array
     */
    public function getArrLeaveReasonOnlyOnetimeMonth()
    {
        return [
            'Bố mẹ, vợ/chồng, con cái qua đời',
        ];
    }


    /**
     * get information leave reason only 1 time
     * @return array
     */
    public function getLeaveReasonOnlyOnetime()
    {
        $arr = $this->getArrLeaveReasonOnlyOnetime();
        return self::whereIn('name', $arr)->get();
    }

    /**
     * get information leave reason only 1 time month
     * @return array
     */
    public function getLeaveReasonOnlyOnetimeMonth()
    {
        $arr = $this->getArrLeaveReasonOnlyOnetimeMonth();
        return self::whereIn('name', $arr)->get();
    }

    
    /**
     * get category leave day reson by name
     *
     * @param  string $name
     * @return collection
     */
    public function getLeaveReasonOnlyByName($name)
    {
        return static::where('name', 'LIKE', '%' . $name . '%')->get();
    }

    public static function checkReasonTeamType($items)
    {
        return in_array($items, [LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE, LeaveDayReason::REASON_RELATIONSHIP_MEMBER_IS_DIE_JA]);
    }

    /**
     * check leave day reason team jp for paid leave
     * @param $reason leave_day_reasons.id
     */
    public static function checkReasonTeamTypeJpPaidLeave($reason)
    {
        return in_array($reason, [LeaveDayReason::REASON_PAID_LEAVE_JA]);
    }
}