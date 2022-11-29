<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form as FormFilter;
use Rikkei\Team\View\Config;

class Timesheet extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'timesheets';
    protected $fillable = [
        'title',
        'project_id',
        'po_id',
        'po_title',
        'start_date',
        'end_date',
        'start_overnight',
        'end_overnight',
        'creator_id',
        'updated_by',
        'status',

        'checkin_standard',
        'checkout_standard',
        'ot_normal_start',
        'ot_normal_end',
        'ot_day_off_start',
        'ot_day_off_end',
        'ot_holiday_start',
        'ot_holiday_end',
        'ot_overnight_start',
        'ot_overnight_end',
    ];
    protected $hidden = [
        'updated_at',
    ];

    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    public static function instance()
    {
        return new self();
    }

    /**
     * Relationship one-many with TimesheetItem
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('Rikkei\Project\Model\TimesheetItem');
    }

    /**
     * Get list status
     * @return array
     */
    public static function getStatus()
    {
        return [
            self::STATUS_DRAFT => trans('project::timesheet.draft'),
            self::STATUS_PUBLISHED => trans('project::timesheet.published'),
        ];
    }

    /**
     * Get List timesheets
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getListTimeSheet($projects)
    {
        $url = route('project::timesheets.index') . '/';
        $filter = FormFilter::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collection = self::query();
        $collection->whereIn('project_id', array_keys($projects));
        if (!empty($dataFilter['start_date']) && !empty($dataFilter['end_date'])) {
            $collection->where(function ($query) use ($dataFilter) {
                $query->where('start_date', '>=', $dataFilter['start_date'])
                    ->orWhere('end_date', '<=', $dataFilter['end_date']);
            });
        } elseif (!empty($dataFilter['start_date'])) {
            $collection->where('start_date', '>=', $dataFilter['start_date']);
        } elseif (!empty($dataFilter['end_date'])) {
            $collection->where('end_date', '<=', $dataFilter['end_date']);
        }

        if (!empty($dataFilter['created_at'])) {
            $collection->whereDate('created_at', '=', $dataFilter['created_at']);
        }

        $pager = Config::getPagerData();

        if (FormFilter::getFilterPagerData('order', $url)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('id', 'desc');
        }

        static::filterGrid($collection, [], $url, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Get Timesheet detail by ID
     *
     * @param $timesheetId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    public function getTimesheetlById($timesheetId)
    {
        return $this->getTimeSheetDetail(['id' => $timesheetId])->first();
    }

    /**
     * Get Timesheet detail for API
     *
     * @param $data
     * @return array
     */
    public function getTimesheetForApi($data)
    {
        $data['status'] = self::STATUS_PUBLISHED;
        $timesheets = $this->getTimeSheetDetail($data)->get();
        $response = [];

        foreach ($timesheets->toArray() as $timesheet) {
            foreach ($timesheet['items'] as $item) {
                foreach ($item['details'] as $detail) {
                    $timekeeping[$item['line_item_id']][] = [
                        'date' => $detail['date'],
                        'checkin' => $detail['checkin'],
                        'checkout' => $detail['checkout'],
                        'working_hour' => $detail['working_hour'],
                        'break_time' => $detail['break_time'],
                        'ot_hour' => $detail['ot_hour'],
                        'holiday' => $detail['holiday'],
                        'overnight' => $detail['overnight'],
                        'note' => $detail['note']
                    ];
                }

                $response[$item['line_item_id']] = [
                    'line_item_id' => $item['line_item_id'],
                    'project_id' => $timesheet['project_id'],
                    'roles' => $item['roles'],
                    'level' => $item['level'],
                    'employee_id' => $item['employee_id'],
                    'division_id' => $item['division_id'],
                    'days_of_leave' => $item['day_of_leave'],
                    'timesheet' => $timekeeping[$item['line_item_id']],
                ];
            }
        }

        return $response;
    }

    /**
     * @param $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getTimeSheetDetail($condition)
    {
        $timesheet = self::query()
            ->with('items.details');

        foreach ($condition as $key => $item) {
            //Nếu điều kiện là array
            // sẽ phải theo format [ 'condition' => '=', 'value' =>'123' ]
            if (is_array($item)) {
                $timesheet->where($key, $item['condition'], $item['value']);
            }

            $timesheet->where($key, $item);
        }

        return $timesheet;
    }
}
