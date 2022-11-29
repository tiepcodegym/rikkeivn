<?php

namespace Rikkei\ManageTime\Console\Commands;;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\ManageTime\Http\Controllers\TimekeepingController;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\View\View;
use Rikkei\Team\Model\Team;

class CheckCompensatoryTimekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:check_compensatory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra làm bù của chi nhánh khi bị update';

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
            $compensations = $this->getArrCompensatory($subNow);
            if (!count($compensations)) {
                return;
            }
            Log::info('=== Start update timekeeping compensatory ===');
            $obj = new TimekeepingController();
            $objView = new View();
            foreach ($compensations as $branch => $compensatory) {
                $tkTable = $this->getTkTable($branch, $subNow);
                if (!count($tkTable)) {
                    continue;
                }
                foreach ($tkTable as $itemTable) {
                    $dataRelate['emp_ids'] = [];
                    $dataRelate['start_date'] = $itemTable->start_date;
                    $dataRelate['end_date'] = $itemTable->end_date;
                    $obj->updateDataRelated($itemTable, $dataRelate);
                    $objView->updateSalaryRateAgregate($itemTable, $dataRelate);
                }
            }
            Log::info('=== End update timekeeping compensatory ===');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * get array compensatory by branch
     * @param $cbDate
     * @return array
     */
    public function getArrCompensatory($cbDate)
    {
        $data = [];
        $date = $cbDate->format("Y-m-d");
        $compensations = CoreConfigData::where('key', 'like', '%compensatory%')
            ->whereDate('updated_at', '=', $date)
            ->get();
        
        if (!count($compensations)) {
            return $data;
        }
        foreach ($compensations as $compensatory) {
            $arrDate = $this->getArrDate($compensatory->value, $cbDate);
            switch ($compensatory->key) {
                case 'project.compensatory.work.hn':
                    $data[Team::CODE_PREFIX_HN] = $arrDate;
                    break;
                case 'project.compensatory.work.dn':
                    $data[Team::CODE_PREFIX_DN] = $arrDate;
                    break;
                case 'project.compensatory.work.hcm':
                    $data[Team::CODE_PREFIX_HCM] = $arrDate;
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
     * @return array
     */
    public function getArrDate($strDate, $cbDate)
    {
        if (!$strDate) {
            return [];
        }
        $cbFirst = clone $cbDate;
        $cpLast = clone $cbDate;
        $cbFirst = $cbFirst->firstOfMonth();
        $cpLast = $cpLast->lastOfMonth();
        $dates = [];
        $arrDate = preg_split('/\;|\r\n|\n|\r/', $strDate);
        foreach ($arrDate as $date) {
            $date = trim($date);
            $arr = explode('=>', $date);
            if (count($arr) == 2) {
                foreach ($arr as $d) {
                    if($d >= $cbFirst->format("Y-m-d") &&
                        $d <= $cpLast->format("Y-m-d")) {
                        $dates[] = $d;
                    }
                }
            }
        }
        return $dates;
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
}
