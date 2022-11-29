<?php

namespace Rikkei\ManageTime\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementEmployee;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;

class ConvertTimeRegisterHcm extends Command
{
    const DATE = '2020-03-01';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert_hcm:timekeeping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'convert time dang ki cac don cho hcm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        DB::beginTransaction();
        try {
            Log::info('=== Start convert time hcm ===');
            $regLeaveDays = $this->getRegLeaveDayHCM();
            $convertLD = $this->convertTime($regLeaveDays);
            $this->updateLeaveDay($convertLD);

            $regSupp = $this->getRegSupplementHCM();
            $convertSupp = $this->convertTime($regSupp);
            $this->updateSupp($convertSupp);
            $this->updateSuppEmp($convertSupp);

            $regBusTrip = $this->getRegBusinessTripHCM();
            $convertBusTrip = $this->convertTime($regBusTrip);
            if (count($regBusTrip)) {
                $idRegBus = $regBusTrip->lists('id')->toArray();
                $regBusTripEmps = $this->getRegBusinessTripEmpHCM($idRegBus);
                $convertBusTripEmps = $this->convertTime($regBusTripEmps);
                $this->updateBusTripEmp($convertBusTripEmps);
                $this->updateBusTrip($convertBusTrip);
            }
            
            Log::info('=== End convert time hcm ===');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->info($e->getMessage());
            Log::error($e);
        }
    }

    /**
     * get all register leave days hcm
     * @return mixed
     */
    public function getRegLeaveDayHCM()
    {
        return $this->getRegiterHCM(LeaveDayRegister::class);
    }

    /**
     * get all register supplement hcm
     * @return mixed
     */
    public function getRegSupplementHCM()
    {
        return $this->getRegiterHCM(SupplementRegister::class);
    }

    /**
     * get all register business trip hcm
     * @return mixed
     */
    public function getRegBusinessTripHCM()
    {
        return $this->getRegiterHCM(BusinessTripRegister::class);
    }

    /**
     *  get all register business trip emp hcm
     *
     * @param array $idReg
     * @return mixed
     */
    public function getRegBusinessTripEmpHCM($idReg)
    {
        $tbl = BusinessTripEmployee::getTableName();

        return BusinessTripEmployee::select(
            "{$tbl}.register_id as id",
            "{$tbl}.employee_id",
            "{$tbl}.start_at",
            "{$tbl}.end_at"
        )
        ->whereDate('end_at', '>=', self::DATE)
        ->whereIn('register_id', $idReg)
        ->get();
    }

    /**
     * get all register with class
     *
     * @param $model
     * @return mixed
     */
    public function getRegiterHCM($model)
    {
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tbl = $model::getTableName();

        return $model::select(
            "{$tbl}.id",
            "{$tbl}.creator_id",
            "{$tbl}.date_start",
            "{$tbl}.date_end",
            "{$tblTeam}.name"
        )
        ->whereDate('date_end', '>=', self::DATE)
        ->leftjoin("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tbl}.creator_id")
        ->leftjoin("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
        ->where('status', '!=', LeaveDayRegister::STATUS_DISAPPROVE)
        ->where("{$tblTeam}.code", 'LIKE', Team::CODE_PREFIX_HCM . '%')
        ->groupBy("{$tbl}.id")
        ->get();
    }

    /**
     * convert time
     *
     * @param $collections
     * @return array
     */
    public function convertTime($collections)
    {
        $array = [];
        if (!count($collections)) {
            return $array;
        }
        foreach ($collections as $item) {
            if (isset($item->date_start)) {
                $keyStart = 'date_start';
                $keyEnd = 'date_end';
            } elseif (isset($item->start_at)) {
                $keyStart = 'start_at';
                $keyEnd = 'end_at';
            } else {
                continue;
            }

            if (isset($item->creator_id)) {
                $empId = $item->creator_id;
            } elseif (isset($item->employee_id)) {
                $empId = $item->employee_id;
            } else {
                continue;
            }

            $start = Carbon::parse($item->$keyStart);
            $end = Carbon::parse($item->$keyEnd);
            if (($this->isTimeStart($start->format('H:i')) && $start->format('Y-m-d') >= self::DATE) ||
                ($this->isTimeEnd($end->format('H:i')) && $end->format('Y-m-d') >= self::DATE)) {
                if ($this->isTimeStart($start->format('H:i')) && $start->format('Y-m-d') >= self::DATE) {
                    $timeStart = $this->changeTime($start->format('H:i'));
                } else {
                    $timeStart = $start->format('H:i');
                }
                $timeStart = $start->format('Y-m-d') . ' ' . $timeStart;
                if ($this->isTimeEnd($end->format('H:i')) && $end->format('Y-m-d') >= self::DATE) {
                    $timeEnd = $this->changeTime($end->format('H:i'));
                } else {
                    $timeEnd = $end->format('H:i');
                }
                $timeEnd = $end->format('Y-m-d') . ' ' . $timeEnd;
                $key = $item->id . '-' . $empId;
                $array[$key]['date_start'] = $timeStart;
                $array[$key]['start_at'] = $timeStart;
                $array[$key]['date_end'] = $timeEnd;
                $array[$key]['end_at'] = $timeEnd;

                $employee = Employee::getEmpById($empId);
                $numberDay = ManageLeaveDay::getTimeLeaveDay($timeStart, $timeEnd, $employee);
                $array[$key]['number'] = $numberDay;
            }
        }
        return $array;
    }

    /**
     * check exists array time start register
     *
     * @param $time
     * @return bool
     */
    public function isTimeStart($time)
    {
        $arrStart = [
            '08:00',
            '10:00',
            '13:30',
            '15:30',
        ];
        if (in_array($time, $arrStart)) {
            return true;
        }
        return false;
    }

    /**
     * check exists array time end register
     *
     * @param $time
     * @return bool
     */
    public function isTimeEnd($time)
    {
        $arrEnd = [
            '10:00',
            '15:30',
            '17:30',
        ];
        if (in_array($time, $arrEnd)) {
            return true;
        }
        return false;
    }

    /**
     * @param $time
     * @return string
     */
    public function changeTime($time)
    {

        switch ($time) {
            case '08:00':
                $timeChange = '08:30';
                break;
            case '10:00':
                $timeChange = '10:30';
                break;
            case '12:00':
                $timeChange = '12:00';
                break;
            case '13:30':
                $timeChange = '13:15';
                break;
            case '15:30':
                $timeChange = '15:45';
                break;
            case '17:30':
                $timeChange = '17:45';
                break;
            default:
                $timeChange = $time;
                break;
        }
        return $timeChange;
    }

    /**
     * update leave day
     * @param $leaveDays
     */
    public function updateLeaveDay($leaveDays)
    {
        $srt = $this->update(LeaveDayRegister::class, $leaveDays, 'number_days_off');
        if ($srt) {
            Log::info('Update cac don phep sau ' . $srt);
        }
    }

    /**
     * @param $supplemnts
     */
    public function updateSupp($supplemnts)
    {
        $srt = $this->update(SupplementRegister::class, $supplemnts, 'number_days_supplement');
        if ($srt) {
            Log::info('Update cac don BSC sau ' . $srt);
        }
    }

    /**
     * @param $supplemnts
     */
    public function updateSuppEmp($supplemnts)
    {
        $this->update(SupplementEmployee::class, $supplemnts, 'number_days', 'supplement_registers_id');
    }

    /**
     * @param $supplemnts
     */
    public function updateBusTrip($businissTrips)
    {
        $srt = $this->update(BusinessTripRegister::class, $businissTrips, 'number_days_business_trip');
        if ($srt) {
            Log::info('Update cac don CT sau ' . $srt);
        }
    }

    /**
     * @param $supplemnts
     */
    public function updateBusTripEmp($businissTripEmps)
    {
        $this->update(BusinessTripEmployee::class, $businissTripEmps, '', 'register_id');
    }

    /**
     * @param $model
     * @param $collections
     * @param string $keyNew
     * @param $removeAt
     * @return string
     */
    public function update($model, $collections, $keyNew = '', $keyTblEmp = '')
    {
        if (!$collections) {
            return '';
        }
        $arrId = [];
        $collections = $this->changeKey($collections, $keyNew);
        foreach ($collections as $key => $data) {
            $arrIdRegEmp = explode('-', $key);
            $arrId[] = $arrIdRegEmp[0];
            if (!$keyTblEmp) {
                unset($data['start_at']);
                unset($data['end_at']);
                $model::where('id', $arrIdRegEmp[0])
                    ->update($data);
            } else {
                unset($data['date_start']);
                unset($data['date_end']);
                $model::where($keyTblEmp, $arrIdRegEmp[0])
                    ->where('employee_id', $arrIdRegEmp[1])
                    ->update($data);
            }
        }
        $srt = implode(",", array_unique($arrId));
        return $srt;
    }

    /**
     * @param $arrays
     * @param string $keyNew
     * @return mixed
     */
    public function changeKey($arrays, $keyNew = '')
    {
        foreach ($arrays as $key => $item) {
            if ($keyNew) {
                $arrays[$key][$keyNew] = $item['number'];
            }
            unset($arrays[$key]['number']);
        }
        return $arrays;
    }
}
