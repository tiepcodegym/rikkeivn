<?php

namespace Rikkei\ManageTime\Console\Commands;;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View;
use Rikkei\Team\Model\Team;

class CheckHolidayTimekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:check_holiday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra ngày lễ của chi nhánh khi bị update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $subNow = Carbon::now()->subDay();
            $holidays = $this->getArrHoliday($subNow);
            if (!count($holidays)) {
                return;
            }

            $timekeepings = $this->getHolidayTk($subNow);
            $tkResetHoliday = [];
            $arrTkTables = [];
            if (count($timekeepings) || count($holidays)) {
                Log::info('=== Start update timekeeping holiday ===');
            }
            if (count($timekeepings)) {
                foreach ($timekeepings as $timekeeping) {
                    foreach ($holidays as $branch => $dates) {
                        if ($timekeeping->branch_code = $branch &&
                            !in_array($timekeeping->timekeeping_date, $dates)) {
                            $tkResetHoliday[$timekeeping->id][] = $timekeeping->timekeeping_date;
                            $arrTkTables[] = $timekeeping;
                        }
                    }
                }
            }
            if ($tkResetHoliday) {
                $this->tkResetHolidayTimekeeping($tkResetHoliday);
            }

            if (!count($holidays) && count($timekeepings)) {
                $this->updateDataRelatedAggregate($arrTkTables);
                Log::info('=== End update timekeeping holiday ===');
                return;
            }

            foreach ($holidays as $branch => $dates) {
                if (!$dates) {
                    continue;
                }
                $this->tkUpdateHoliday($branch, $subNow, $dates);
                $tkTables = $this->getTkTable($branch, $subNow);
                if (!count($tkTables)) {
                    continue;
                }
                foreach ($tkTables as $itemTable) {
                    $arrTkTables[] = $itemTable;
                }
            }
            $this->updateDataRelatedAggregate($arrTkTables);
            Log::info('=== End update timekeeping holiday ===');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * get array holiday by branch
     * @param $cbDate
     * @return array
     */
    public function getArrHoliday($cbDate)
    {
        $data = [];
        $date = $cbDate->format("Y-m-d");
        $holidays = CoreConfigData::where('key', 'like', '%holidays%')
            ->whereDate('updated_at', '=', $date)
            ->get();
        $holidaysAnnual= CoreConfigData::where('key', 'like', 'project.annual_holidays')->first();
        $arrAnnual = $this->getArrDate($holidaysAnnual->value, $cbDate);
        if ($holidaysAnnual->updated_at->format("Y-m-d") == $date) {
            $holidays = CoreConfigData::where('key', 'like', '%holidays%')
                ->where('key', '<>', 'project.annual_holidays')
                ->where('key', '<>', 'project.special_holidays')
                ->get();
        }
        if (!count($holidays)) {
            return $data;
        }
        foreach ($holidays as $holiday) {
            $arrDate = $this->getArrDate($holiday->value, $cbDate);
            $value = array_merge($arrAnnual, $arrDate);
            switch ($holiday->key) {
                case 'project.special_holidays_hn':
                    $data[Team::CODE_PREFIX_HN] = $value;
                    break;
                case 'project.special_holidays_dn':
                    $data[Team::CODE_PREFIX_DN] = $value;
                    break;
                case 'project.special_holidays_hcm':
                    $data[Team::CODE_PREFIX_HCM] = $value;
                    break;
                default:
                    break;
            }
        }
        return $data;
    }

    /**
     * convert string to array
     * @param $strDate
     * @param $cbDate
     * @return array
     */
    public function getArrDate($strDate, $cbDate)
    {
        if (!$strDate) {
            return [];
        }
        $dates = [];
        $cbFirst = clone $cbDate;
        $cpLast = clone $cbDate;
        $cbFirst = $cbFirst->firstOfMonth();
        $cpLast = $cpLast->lastOfMonth();
        $arrDate = preg_split('/\;|\r\n|\n|\r/', $strDate);
        foreach ($arrDate as $date) {
            $date = trim($date);
            $arr = explode('-', $date);
            $dateHoliday = '';
            if (count($arr) == 2) {
                $dateHoliday = $cbDate->year . '-' . $date;
            } elseif (count($arr) == 3) {
                $dateHoliday = $date;
            } else {
            }
            if ($dateHoliday &&
                $dateHoliday >= $cbFirst->format("Y-m-d") &&
                $dateHoliday <= $cpLast->format("Y-m-d")) {
                $dates[] = $dateHoliday;
            }
        }
        return $dates;
    }

    /**
     * get holiday table timekeepings
     * @param $cbDate
     * @return Collection
     */
    public function getHolidayTk($cbDate)
    {
        $month = $cbDate->month;
        $year = $cbDate->year;
        $tkTable = TimekeepingTable::getTableName();
        return TimekeepingTable::select(
            "{$tkTable}.id",
            "{$tkTable}.team_id",
            "{$tkTable}.month",
            "{$tkTable}.year",
            "{$tkTable}.type",
            "{$tkTable}.end_date",
            "{$tkTable}.start_date",
            "tk.timekeeping_date",
            "teams.name",
            "teams.branch_code"
        )
        ->leftJoin('manage_time_timekeepings as tk', 'tk.timekeeping_table_id', '=', "{$tkTable}.id")
        ->leftJoin('teams', 'teams.id', '=', "{$tkTable}.team_id")
        ->where("{$tkTable}.month", $month)
        ->where("{$tkTable}.year", $year)
        ->where("{$tkTable}.type", TimekeepingTable::OFFICIAL)
        ->where("tk.timekeeping", ManageTimeConst::HOLIDAY_TIME)
        ->groupBy('timekeeping_date')
        ->groupBy('timekeeping_table_id')
        ->get();
    }

    /**
     * @param $dataReset
     */
    public function tkResetHolidayTimekeeping($dataReset)
    {
        foreach ($dataReset as $tkTableId => $dates) {
            Timekeeping::whereIn('timekeeping_date', $dates)
            ->where('timekeeping_table_id', $tkTableId)
            ->update([
                'timekeeping' => 0,
                'timekeeping_number' => 0,
            ]);
        }
        return;
    }

    /**
     * @param $branch
     * @param $cbDate
     * @param $dates
     * @return Collection
     */
    public function tkUpdateHoliday($branch, $cbDate, $dates)
    {
        $month = $cbDate->month;
        $year = $cbDate->year;
        $tkTable = TimekeepingTable::getTableName();
        return DB::table("{$tkTable}")->leftJoin('manage_time_timekeepings as tk', 'tk.timekeeping_table_id', '=', "{$tkTable}.id")
        ->leftJoin('teams', 'teams.id', '=', "{$tkTable}.team_id")
        ->where("{$tkTable}.month", $month)
        ->where("$tkTable.year", $year)
        ->where("$tkTable.type", TimekeepingTable::OFFICIAL)
        ->where("teams.branch_code", $branch)
        ->whereIn("tk.timekeeping_date", $dates)
        ->update([
            'tk.timekeeping' => ManageTimeConst::HOLIDAY_TIME,
            'tk.timekeeping_number' => ManageTimeConst::FULL_TIME,
            'tk.updated_at' => $cbDate,
        ]);
    }

    /**
     * @param $branch
     * @param $cbDate
     * @return mixed
     */
    public function getTkTable($branch, $cbDate)
    {
        $month = $cbDate->month;
        $year = $cbDate->year;
        $tkTable = TimekeepingTable::getTableName();
        return TimekeepingTable::select(
            "{$tkTable}.id",
            "{$tkTable}.timekeeping_table_name",
            "{$tkTable}.team_id",
            "{$tkTable}.month",
            "{$tkTable}.year",
            "{$tkTable}.type",
            "{$tkTable}.start_date",
            "{$tkTable}.end_date",
            "teams.name",
            "teams.branch_code"
        )
        ->leftJoin('teams', 'teams.id', '=', "{$tkTable}.team_id")
        ->where("{$tkTable}.month", $month)
        ->where("$tkTable.year", $year)
        ->where("teams.branch_code", $branch)
        ->get();
    }

    /**
     * @param $tkTables
     * @throws Exception
     */
    public function updateDataRelatedAggregate($tkTables)
    {
        if (!count($tkTables)) {
            return;
        }
        $obj = new TimekeepingController();
        // $objView = new View();
        $id = [];
        foreach ($tkTables as $key => $tkTable) {
            if (in_array($tkTable->id, $id)) {
                unset($tkTables[$key]);
            } else {
                $id[] = $tkTable->id;
            }
        }
        foreach ($tkTables as $itemTable) {
            $dataRelate['emp_ids'] = [];
            $dataRelate['start_date'] = $itemTable->start_date;
            $dataRelate['end_date'] = $itemTable->end_date;
            $obj->updateDataRelated($itemTable, $dataRelate);
            // $objView->updateSalaryRateAgregate($itemTable, $dataRelate);
        }
         return;
    }
}
