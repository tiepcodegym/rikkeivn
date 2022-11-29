<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimekeepingWorkingTime extends CoreModel
{
    use SoftDeletes;

    protected $table = 'timekeeping_working_time';
    protected $fillable = [
        'timekeeping_table_id',
        'working_time_id',
        'employee_id',
        'from_date',
        'to_date',
        'start_time1',
        'end_time1',
        'start_time2',
        'end_time2',
        'half_morning',
        'half_afternoon'
    ];
        
    /**
     * insert
     *
     * @param  int $tkTableId
     * @param  array $empIds
     * @param  date $dateStart
     * @param  date $dateEnd
     * @return void
     */
    public function insertWKT($tkTableId, $empIds, $dateStart, $dateEnd)
    {
        $now = Carbon::now();
        $strEmpId = trim(implode(',', $empIds), ",");
        $status = WorkingTimeRegister::STATUS_APPROVE;
        static::where('timekeeping_table_id', $tkTableId)
            ->whereIn('employee_id', $empIds)
            ->whereDate('from_date', '<=', $dateEnd)
            ->whereDate('to_date', '>=', $dateStart)
            ->update(['deleted_at' => $now]);

        $sql = "INSERT INTO timekeeping_working_time(timekeeping_table_id,working_time_id,employee_id,from_date,to_date,start_time1,end_time1,start_time2,end_time2,half_morning,half_afternoon,created_at)
        select {$tkTableId},
            working_time_details.working_time_id,
            working_time_details.employee_id,
            working_time_details.from_date,
            working_time_details.to_date,
            working_time_details.start_time1,
            working_time_details.end_time1,
            working_time_details.start_time2,
            working_time_details.end_time2,
            working_time_details.half_morning,
            working_time_details.half_afternoon,
            '{$now}'
        from working_time_details 
        inner join working_time_registers on working_time_registers.id = working_time_details.working_time_id
        where working_time_details.employee_id in ({$strEmpId}) 
            and date(working_time_details.from_date) <= '{$dateEnd}'
            and date(working_time_details.to_date) >= '{$dateStart}' 
            and working_time_registers.deleted_at is null
            and working_time_registers.status = {$status}
            and working_time_details.deleted_at is null";
        DB::statement($sql);
        return;
    }
        
    /**
     * getWKTTimeKeepingEmployee
     *
     * @param  array $empIds
     * @param  int $tkTableId
     * @param  date $startDate
     * @param  date $endDate
     * @return array
     */
    public function getWKTTimeKeepingEmployee($tkTableId, $empIds, $startDate, $endDate)
    {
        return static::select(
            'working_time_id',
            'employee_id',
            'from_date as wtk_from_date',
            'to_date as wtk_to_date',
            'half_morning',
            'half_afternoon',
            'start_time1',
            'end_time1',
            'start_time2',
            'end_time2'
        )
        ->where('timekeeping_table_id', $tkTableId)
        ->whereIn('employee_id', $empIds)
        ->whereDate('from_date', '<=', $endDate)
        ->whereDate('to_date', '>=', $startDate)
        ->whereNull('deleted_at')
        ->get()->groupBy('employee_id');
    }
}
