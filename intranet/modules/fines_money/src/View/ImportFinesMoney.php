<?php
namespace Rikkei\FinesMoney\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Rikkei\FinesMoney\Model\FinesActionHistory;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\FinesMoney\Model\JobFinesMoney;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Storage;

class ImportFinesMoney
{
    const FOLDER_UPLOAD = 'upload_fines_money';
    const FOLDER_LOG = 'fines_import_log';
    const ACCESS_FOLDER = 0777;
    const CHUNK_ROW = 200;
    /**
     * Handle import fines money
     *
     * @param $rows
     * @throws \Exception
     */
    public function importFileBySheet($rows)
    {
        if (count($rows) == 2) {
            if (isset($rows[0]) && !empty($rows[0])) {
                $this->importBySheet($rows[0]);
            }
            if (isset($rows[1]) && !empty($rows[1])) {
                $this->importBySheet($rows[1]);
            }
        } else {
            $this->importBySheet($rows);
        }
    }

    /**
     * @return array
     */
    public function headingDefine()
    {
        return [
            trans('fines_money::view.employee_code'),
        ];
    }

    /**
     * @param $arrayHeading
     * @return bool
     */
    public function checkHeading($arrayHeading)
    {
        $check = true;
        $headingDefine = $this->headingDefine();
        $arrGeneral = array_intersect_assoc($arrayHeading, $headingDefine);
        $checkFormat = array_diff_assoc($headingDefine, $arrGeneral);
        if (!empty($checkFormat)) {
            $check = false;
        }
        return $check;
    }

    /**
     * @param array $rows
     * @return array
     */
    public function importBySheet($rows)
    {
        $data = [];
        $heading = [];
        $dataInsert = [];
        $obFinesMoney = new FinesMoney();
        foreach ($rows as $key => $row) {
            $data[trim(strtolower($row[0]))] = $row ? $row : null; // push data money with key ma_vn
            foreach ($row as $k => $value) {
                if (preg_match('/^\d{2}\/\d{4}$/', $value)) { //get month and year co the thay doi theo tuy file excel
                    $time = explode('/', $value);
                    $heading[$k] = [
                        'month' => (int)$time[0],
                        'year' => (int)$time[1],
                    ];
                }
            }
        }
        if (empty($heading)) {
            throw new \Exception(Lang::get('fines_money::view.Not data money'));
        }
        $listEmps = Employee::whereIn(DB::raw('SUBSTRING(email, 1, LOCATE("@", email) - 1)'), array_keys($data))
            ->pluck('id', DB::raw("SUBSTRING(email, 1, LOCATE('@', email) - 1) as email"))
            ->toArray();
        //get prefix employee
        $prefixByEmpIds = $this->getPrefixEmployees($listEmps);
        foreach ($heading as $v => $item) {
            foreach ($data as $key => $value) {
                if (isset($listEmps[$key]) && is_numeric($data[$key][$v])) {
                    $empId = $listEmps[$key];
                    $onlyPrefix = isset($prefixByEmpIds[$empId]) ? $prefixByEmpIds[$empId] : Team::CODE_PREFIX_HN;
                    $checkItem = $obFinesMoney->getItemByCondition($empId, $item['month'], $item['year'], FinesMoney::TYPE_LATE);
                    if (!$checkItem->first() && is_numeric($data[$key][$v])) {
                        $dataInsert[] = [
                            'month' => $item['month'],
                            'year' => $item['year'],
                            'employee_id' => $empId,
                            'status_amount' => FinesMoney::STATUS_UN_PAID,
                            'type' => FinesMoney::TYPE_LATE,
                            'amount' => $data[$key][$v],
                            'count' => $this->getBlogByPrefixAndMoney($data[$key][$v], $onlyPrefix),
                        ];
                    }
                }
            }
        }
        if (!empty($dataInsert)) {
            FinesMoney::insert($dataInsert);
        }
    }

    /**
     * Get blog by money and prefix team
     *
     * @param integer $money
     * @param string $prefix
     * @return float|int
     */
    public function getBlogByPrefixAndMoney($money, $prefix)
    {
        $objTimeCons = new ManageTimeConst();
        $block = $objTimeCons->getFinesBlockBranch($prefix);
        return (int)$money / $block;
    }

    /**
     * Update fines money
     *
     * @param array $rows
     * @param array $titleIndex
     * @param integer $curEmp
     */
    public function importUpdate($rows, &$titleIndex, $curEmp)
    {
        $empCodes = [];
        $histories = [];
        $result = [];
        $obFinesMoney = new FinesMoney();
        $listStatus = $obFinesMoney->getStatus();
        $types = $obFinesMoney->getTypes();
        $hisFines = new FinesActionHistory();

        foreach ($rows as $key => $row) {
            $rowTmp = array_values($row->toArray());

            if (!$rowTmp[$titleIndex['employee_code']]
                || !$rowTmp[$titleIndex['month']]
                || !$rowTmp[$titleIndex['year']]
                || !$rowTmp[$titleIndex['type']]
                || !is_numeric($rowTmp[$titleIndex['amount']])
                || !$rowTmp[$titleIndex['status']]) {
                continue;
            }
            $result[] = $rowTmp;
            $empCodes[] = trim($rowTmp[$titleIndex['employee_code']]);
        }
        $listEmpIds = DB::table('employees')->whereIn('employee_code', $empCodes)
            ->pluck('id', 'employee_code');

        $prefixByEmpIds = $this->getPrefixEmployees($listEmpIds);
        foreach ($result as $key => $row) {
            if (!$row[$titleIndex['employee_code']]
                || !$row[$titleIndex['month']]
                || !$row[$titleIndex['year']]
                || !$row[$titleIndex['type']]
                || !is_numeric($row[$titleIndex['amount']])
                || !$row[$titleIndex['status']]) {
                continue;
            }
            $empCode = trim($row[$titleIndex['employee_code']]);
            if (isset($listEmpIds[$empCode]) && $listEmpIds[$empCode]) {
                $employeeId = $listEmpIds[$empCode];
                $valStatus = $this->getValByText(trim($row[$titleIndex['status']]), $listStatus);
                $valType = $this->getValByText(trim($row[$titleIndex['type']]), $types);
                if (!is_numeric($valType) || !is_numeric($valStatus)) {
                    continue;
                }
                //get prefix employee => money by bock
                $onlyPrefix = (isset($prefixByEmpIds[$employeeId]) && $prefixByEmpIds[$employeeId]) ? $prefixByEmpIds[$employeeId] : Team::CODE_PREFIX_HN;
                $itemUpdate = $obFinesMoney->getItemByCondition($employeeId, $row[$titleIndex['month']], $row[$titleIndex['year']], $valType)->first();

                //check change money or note, status ( special: money with tye == 1 not change)
                if (!empty($itemUpdate) && (
                        $row[$titleIndex['note']] != $itemUpdate->note ||
                        $itemUpdate->status_amount != $valStatus ||
                        ($valType == FinesMoney::TYPE_LATE && is_numeric($row[$titleIndex['amount']]) && $itemUpdate->amount != trim($row[$titleIndex['amount']])))
                ) {
                    $contentHis = $hisFines->getContentHistory($itemUpdate->toArray(), [
                        'status_amount' => $valStatus,
                        'note' => !empty(trim($row[$titleIndex['note']])) ? trim($row[$titleIndex['note']]) : null,
                        'amount' => $row[$titleIndex['amount']],
                    ]);

                    $itemUpdate->status_amount = $valStatus;
                    $itemUpdate->note = trim($row[$titleIndex['note']]) ? trim($row[$titleIndex['note']]) : null;
                    if ($valType == FinesMoney::TYPE_LATE) {
                        $itemUpdate->amount = $row[$titleIndex['amount']];
                        $itemUpdate->count = $this->getBlogByPrefixAndMoney(trim($row[$titleIndex['amount']]), $onlyPrefix);
                    }
                    $itemUpdate->save();
                    $this->pushDataHistory($histories, $itemUpdate, $contentHis, $curEmp);
                }
                DB::table('fines_action_history')->insert($histories);
            }
        }

        //Check status & update count if run queue
        $jobFinesMoney = new JobFinesMoney();
        $jobFinesMoney->checkJobSuccess($curEmp);

    }

    /**
     * @param array $histories
     * @param object $fineMoney
     * @param string $logContent
     * @param object $curEmpId
     * @return int
     */
    public function pushDataHistory(&$histories, $fineMoney, $logContent, $curEmpId)
    {
        return array_push($histories, [
            'fines_money_id' => $fineMoney->id,
            'checker_id' => $curEmpId,
            'object_fines' => $fineMoney->employee_id,
            'action' => $fineMoney->status_amount,
            'amount' => $fineMoney->amount,
            'month' => $fineMoney->month,
            'type' => $fineMoney->type,
            'year' => $fineMoney->year,
            'checked_date' => Carbon::now(),
            'content' => $logContent,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }

    /**
     * Get prefix code of list employee_ids
     *
     * @param array $listEmps
     * @return array
     */
    public function getPrefixEmployees($listEmps = [])
    {
        $prefixByEmpCode = [];
        $sql = "(CASE
                    WHEN LOCATE('_', code) = 0 THEN code
                    ELSE SUBSTRING(teams.code, 1, LOCATE('_', code) - 1)
                    END
                ) AS `code`";
        if (!empty($listEmps)) {
            $listTeam = Team::join('team_members', 'team_members.team_id', '=', 'teams.id')
                ->whereIn('team_members.employee_id', $listEmps)
                ->select(
                    'team_members.team_id', 'employee_id', DB::raw($sql)
                )
                ->groupBy(['team_members.team_id', 'team_members.employee_id'])
                ->get();
            if ($listTeam) {
                foreach ($listTeam as $item) {
                    if (!isset($prefixByEmpCode[$item->employee_id])) {
                        $prefixByEmpCode[$item->employee_id][] = $item->code;
                    }
                    if ($prefixByEmpCode[$item->employee_id] && $item->code && !in_array($item->code, $prefixByEmpCode[$item->employee_id])) {
                        $prefixByEmpCode[$item->employee_id][] = $item->code;
                    }
                }
            }
        }
        $prefixByEmpId = [];
        foreach ($prefixByEmpCode as $key => $item) {
            if (!empty($item)) {
                if (in_array(Team::CODE_PREFIX_JP, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_JP;
                } elseif (in_array(Team::CODE_PREFIX_DN, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_DN;
                } elseif (in_array(Team::CODE_PREFIX_HCM, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_HCM;
                } elseif (in_array(Team::CODE_PREFIX_AI, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_AI;
                } elseif (in_array(Team::CODE_PREFIX_RS, $item)) {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_RS;
                } else {
                    $prefixByEmpId[$key] = Team::CODE_PREFIX_HN;
                }
            } else {
                $prefixByEmpId[$key] = Team::CODE_PREFIX_HN;
            }
        }
        return $prefixByEmpId;
    }

    /**
     * @return array
     */
    public function getHeadingIndexFines()
    {
        return [
            'no' => 0,
            'employee_code' => 1,
            'employee_name' => 2,
            'month' => 3,
            'year' => 4,
            'type' => 5,
            'amount' => 6,
            'status' => 7,
            'note' => 8,
        ];
    }

    /**
     * @param $folder
     * @param $file
     * @return string
     * @throws \Exception
     */
    public function storeFile($folder, $file)
    {
        if (!$folder) {
            $folder = self::FOLDER_UPLOAD;
        }

        try {
            if (!Storage::exists($folder)) {
                Storage::makeDirectory($folder, self::ACCESS_FOLDER);
                @chmod(storage_path('app/' . $folder), self::ACCESS_FOLDER);
            }
            $fileName = auth()->id() . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            Storage::put(
                self::FOLDER_UPLOAD . '/' . $fileName,
                file_get_contents($file->getRealPath()),
                'public'
            );

            return self::FOLDER_UPLOAD . '/' . $fileName;
        } catch (\Exception $ex) {
            Log::error($ex);
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Delete file error day ago
     */
    public function getFileErrorDayAgo()
    {
        $files = JobFinesMoney::where('created_at', '<', Carbon::now()->subDay())->get();
        foreach ($files as $item) {
            $path = storage_path('app/' . $item->file);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Get value from text excel
     * @param $itemCheck
     * @param $arrNeed
     * @return false|int|string
     */
    public function getValByText($itemCheck, $arrNeed)
    {
        $itemCheck = preg_replace('/\s+/', ' ', $itemCheck);
        $arrOutPut = [];
        foreach ($arrNeed as $key => $value) {
            $arrOutPut[$key] = mb_strtolower($value, 'UTF-8');
        }
        return array_search(mb_strtolower($itemCheck, 'UTF-8'), $arrOutPut);
    }
}