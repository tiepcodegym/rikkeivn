<?php

namespace Rikkei\Api\Helper;

use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Team\Model\Team as TeamModel;
use Rikkei\Contract\Model\ContractModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class HrmCommon extends HrmBase
{

    /**
     *
     */
    const STATUS_LEFT_COMPANY = 1;
    /**
     *
     */
    const STATUS_PENDING_LEFT_COMPANY = 0;

    /**
     * Get tất cả chi nhánh
     *
     * @return mixed
     */
    public function getBranches()
    {
        return TeamModel::select(['id', 'name', 'branch_code'])->where('is_branch', 1)->get();
    }

    public function saveContract($data, $hrmContractId = null, $submit)
    {
        DB::beginTransaction();
        try {
            if ($submit == 'update') {
                $modelContract = ContractModel::where('hrm_contract_id', $hrmContractId)->first();
                if (!$modelContract) {
                    throw new Exception('Contract not found');
                }
            } else {
                $modelContract = new ContractModel();
            }

            $modelContract->employee_id = $data['empId'];
            $modelContract->type = $data['type'];
            $modelContract->created_id = $data['creatorId'];
            $modelContract->hrm_contract_id = $data['hrm_contract_id'];

            $startAt = isset($data['from_date']) && trim($data['from_date']) != '' ? Carbon::parse($data['from_date']) : null;
            $modelContract->start_at = $startAt;
            if (isset($data['to_date']) && trim($data['to_date']) != '') {
                $endAt = Carbon::parse($data['to_date']);
            } else {
                $endAt = null;
            }
            $modelContract->end_at = $endAt;
            $resp = $modelContract->save();
            if (!$resp) {
                throw new Exception('System error');
            }

            $modelContract->saveHistory();
            DB::commit();
            return $modelContract;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
