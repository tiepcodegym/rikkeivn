<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeContractHistory extends CoreModel
{
    protected $table = 'employee_contract_histories';
    protected $fillable = [
        'employee_id',
        'employee_code',
        'employee_card_id',
        'contract_type',
        'start_date',
        'end_date',
        'join_date',
        'official_date',
        'leave_date',
        'team_name',
    ];

    /**
     * insert or update item
     * @param object $employee after update
     * @param array|object $data before update
     * @return void
     */
    public static function insertItem($dataNew, $dataOld = null)
    {
        if (!isset($dataNew['contract_type']) || !$dataNew['contract_type']) {
            return;
        }
        $oldContract = $dataOld['contract_type'];
        $newContract = $dataNew['contract_type'];
        $rangeDateOld = self::getOldRangeDateContract($oldContract, $dataOld); //start/end date của hợp đồng cũ.
        $endDate = self::getStartDateContract($newContract, $dataNew);
        $lastItem = self::getLastItem($dataNew->id);
        //đã đươc lưu lịch sử
        if ($lastItem) {
            //leave history
            if ($oldContract != $newContract) { // có thay đổi hợp đồng
                $dataUpdate = [
                    'end_date' => $rangeDateOld[1]
                ];
            } else { // ko thay đổi loại hợp đồng
                $dataUpdate = [
                    'employee_code' => $dataNew->employee_code,
                    'employee_card_id' => $dataNew->employee_card_id,
                    'join_date' => self::toDate($dataNew->join_date),
                    'official_date' => self::toDate($dataNew->offcial_date),
                    'team_name' => $dataNew->getTeamNames()
                ];
                // nếu ở trang candidate detail, tab employee chọn vào "Là nhân viên cũ"
                if (isset($dataOld['come_back']) && $dataOld['come_back']) {
                    $dataUpdate['leave_date'] = $dataOld['leave_date'];
                } else {
                    $dataUpdate['leave_date'] = self::toDate($dataNew->leave_date);
                }
            }
            $lastItem->update($dataUpdate);
        } else {
            if ($oldContract !== null) {
                $itemCreate = $dataOld;
                if ($oldContract == $newContract) {
                    $itemCreate = $dataNew;
                }
                self::create([
                    'employee_id' => $itemCreate->id,
                    'employee_code' => $itemCreate['employee_code'],
                    'employee_card_id' => $itemCreate['employee_card_id'],
                    'contract_type' => $dataOld['contract_type'],
                    'start_date' => $rangeDateOld[0],
                    'end_date' => $rangeDateOld[1],
                    //leave history
                    'join_date' => isset($itemCreate['join_date']) ? self::toDate($itemCreate['join_date']) : null,
                    'official_date' => isset($itemCreate['offcial_date']) ? self::toDate($itemCreate['offcial_date']) : null,
                    'leave_date' => isset($itemCreate['leave_date']) ? self::toDate($itemCreate['leave_date']) : null,
                    'team_name' => isset($itemCreate['team_name']) ? $itemCreate['team_name'] : null,
                ]);
            }
        }

        if ($oldContract != $newContract || (isset($dataOld['come_back']) && $dataOld['leave_date'])) { //cum back is real
            $workingLows = [getOptions::WORKING_INTERNSHIP, getOptions::WORKING_PROBATION];
            if (in_array($oldContract, $workingLows) && !in_array($newContract, $workingLows)) {
                $endDate = $dataOld['offcial_date'] ? $dataOld['offcial_date'] : Carbon::now()->toDateString();
            }
            self::create([
                'employee_id' => $dataNew->id,
                'employee_code' => $dataNew->employee_code,
                'employee_card_id' => $dataNew->employee_card_id,
                'contract_type' => $dataNew['contract_type'],
                'start_date' => $endDate,
                'join_date' => self::toDate($dataNew->join_date),
                'official_date' => self::toDate($dataNew->offcial_date),
                'leave_date' => self::toDate($dataNew->leave_date),
                'team_name' => $dataNew->getTeamNames()
            ]);
        }
    }

    /*
     * convert to date
     */
    public static function toDate($strDate)
    {
        if (!$strDate) {
            return null;
        }
        return Carbon::parse($strDate)->toDateString();
    }

    /**
     * get start date, end date by contract type
     * @param type $oldContract
     * @param type $employee
     * @return type
     */
    public static function getOldRangeDateContract($oldContract, $employee)
    {
        $timeNow = Carbon::now()->toDateTimeString();
        switch ($oldContract) {
            case getOptions::WORKING_INTERNSHIP:
                $candidate = Candidate::getCandidateByEmployee($employee->id);
                if ($candidate) {
                    return [
                        $candidate->trainee_start_date ? $candidate->trainee_start_date : $employee->join_date,
                        $candidate->trainee_end_date ? $candidate->trainee_end_date : $timeNow
                    ];
                }
                return [$employee->join_date, $timeNow];
            case getOptions::WORKING_PROBATION:
                return [
                    $employee->trial_date ? $employee->trial_date : $employee->join_date,
                    $employee->trial_end_date ? $employee->trial_end_date : $timeNow
                ];
            case getOptions::WORKING_OFFICIAL:
            case getOptions::WORKING_UNLIMIT:
            case getOptions::WORKING_BORROW:
            case getOptions::WORKING_PARTTIME:
                return [
                    $employee->offcial_date ? $employee->offcial_date : $employee->join_date,
                    $employee->leave_date ? $employee->leave_date : $timeNow
                ];
            default:
                return [$employee->join_date, $timeNow];
        }
    }

    /**
     * get start date by contract type
     * @param type $newContract
     * @param type $employee
     * @return type
     */
    public static function getStartDateContract($newContract, $employee)
    {
        $timeNow = Carbon::now()->toDateTimeString();
        switch ($newContract) {
            //học việc lấy ngày bắt đầu học việc bên candidate
            case getOptions::WORKING_INTERNSHIP:
                $candidate = Candidate::getCandidateByEmployee($employee->id);
                if ($candidate && $candidate->trainee_start_date) {
                    return $candidate->trainee_start_date;
                }
            //case thử việc, lấy ngày bắt đầu thử việc
            case getOptions::WORKING_PROBATION:
                if ($employee->trial_date) {
                    return $employee->trial_date;
                }
            //hợp đồng chính thức, mượn, part time lấy ngày join date
            case getOptions::WORKING_OFFICIAL:
            case getOptions::WORKING_UNLIMIT:
            case getOptions::WORKING_BORROW:
            case getOptions::WORKING_PARTTIME:
                if ($employee->join_date) {
                    return $employee->join_date;
                }
            default:
                return $timeNow;
        }
    }

    /**
     * get latest item by employee
     * @param type $employeeId
     * @return type
     */
    public static function getLastItem($employeeId)
    {
        return self::where('employee_id', $employeeId)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
    }

    /**
     * get contract type label
     * @param type $list
     * @return type
     */
    public function getContractLabel($list = [])
    {
        if (isset($list[$this->contract_type])) {
            return $list[$this->contract_type];
        }
        return $this->contract_type;
    }

    /**
     * get list history by employee
     * @param type $employeeId
     * @return type
     */
    public static function getByEmp($employeeId)
    {
        return self::select(
            'id',
            'contract_type',
            'employee_code',
            'employee_card_id',
            DB::raw('DATE(start_date) as start_at'),
            DB::raw('DATE(end_date) as end_at'),
            'join_date',
            'official_date',
            'leave_date',
            'team_name'
        )
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
