<?php

namespace Rikkei\Contract\Model;

use DB;
use Exception;
use Carbon\Carbon;
use Rikkei\Contract\Model\ContractHistoryModel;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\Log;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee as EmployeeBase;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Contract\Model\SynchronizeLog;
use Illuminate\Support\Facades\Auth;
use Rikkei\Team\Model\TeamMember;

class EmployeeModel extends EmployeeBase
{

    public function contract()
    {
        return $this->hasMany(ContractModel::class, 'employee_id', 'id');
    }

    public function teamMember()
    {
        return $this->hasMany(TeamMember::class,'employee_id','id');
    }

    /**
     * get employee follow id
     *
     * @param type $id
     * @return type
     */
    public static function getEmpById($id)
    {
        if ($emp = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $emp;
        }
        $emp = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $emp, $id);
        return $emp;
    }

    /**
     * <b>Các trường hợp xử lý khi đẩy thông tin hợp đồng đồng bộ sang profile nhân viên:</b>
     * Các trường hợp xử lý khi đẩy thông tin hợp đồng đồng bộ sang profile nhân viên:
     *  <ul>
     *       <li><b>I. Khi nhấn đồng bộ hợp đồng vào profile hệ thống update loại hợp đồng vào profile</b></li>
     *       <li>
     *               <b>II. Hợp đồng hiện tại là học việc/thuê ngoài</b>
     *               <ol>
     *                   <li>Hợp đồng chọn đồng bộ là thử việc:
     *                       <br/>- Update lại thời gian bắt đầu thử việc và kết thúc thử việc
     *                       <br/>- Update ngày bắt đầu chính thức  = ngày kết thúc thử việc +1 ngày
     *                   </li>
     *                   <li>Hợp đồng chọn đồng bộ là chính thức:
     *                       <br/>- Update ngày bắt đầu chính thức và ngày kết thúc chính thức
     *                   </li>
     *                   <li>Hợp đồng khác:
     *                       <br/>- Hệ thống bỏ qua không xử lý
     *                   </li>
     *               </ol>
     *       </li>
     *       <li>
     *               <b>III. Hợp đồng hiện tại là thử việc</b>
     *               <ol>
     *                   <li>Hợp đồng chọn đồng bộ là thử việc:
     *                       <br/>- Update lại thời gian kết thúc thử việc
     *                       <br/>- Update lại thời gian bắt đầu thử việc (nếu ngày bắt đầu thử việc của hợp đồng đang chọn < ngày bắt đầu thử việc trong profile)
     *                       <br/>- Update lại thời gian bắt đầu chính thức = ngày kết thúc thử +1 ngày
     *                   </li>
     *                   <li>Hợp đồng chọn đồng bộ là chính thức (Có thời hạn hoặc không thời hạn):
     *                       <br/>- Update lại thời gian kết thúc thử việc = ngày kết thúc thử việc  -1 ngày ( nếu ngày bắt đầu chính thức < ngày kết thúc thử việc hiện tại)
     *                       <br/>- Update ngày bắt đầu chính thức
     *                   </li>
     *                   <li>Hợp đồng khác:
     *                       <br/>- Hệ thống không xử lý
     *                   </li>
     *               </ol>
     *       </li>
     *       <li>
     *               <b>IV. Hợp đồng hiện tại là thời vụ</b>
     *               <ol>
     *                   <li>Hợp đồng chọn đồng bộ là thử việc:
     *                       <br/>-  Update lại thời gian bắt đầu và kết thúc thử việc
     *                      <br/>-  Update lại thời gian bắt đầu chính thức = ngày kết thúc thử việc +1 ngày
     *                   </li>
     *                   <li>Hợp đồng chọn đồng bộ là chính thức (Có thời hạn hoặc không thời hạn):
     *                       <br/>- Update lại ngày bắt đầu chính thức nếu ( ngày bắt đầu chính thức của hợp đồng đang chọn < ngày bắt đầu chính thức trong profile)
     *                       <br/>- Update ngày kết thúc thử việc = ngày bắt đầu chính thức -1 ngày (Nếu ngày bắt đầu kết thúc thử việc tồn tại và ngày bắt đầu chính thức <= ngày kết thúc thử việc trong profile)
     *                   </li>
     *                   <li>Hợp đồng khác:
     *                       <br/>- Hệ thống bỏ qua không xử lý
     *                   </li>
     *               </ol>
     *       </li>
     *       <li>
     *               <b>V. Hợp đồng hiện tại chính thức có hạn hoặc không có thời hạn</b>
     *               <ol>
     *                   <li>Hợp đồng hiện tại chính thức có hạn hoặc không có thời hạn
     *                       <br/>- Update lại ngày bắt đầu chính thức nếu ( ngày bắt đầu chính thức của hợp đồng đang chọn < ngày bắt đầu chính thức trong profile)
     *                       <br/>- Update ngày kết thúc thử việc = ngày bắt đầu chính thức -1 ngày (Nếu ngày bắt đầu kết thúc thử việc tồn tại và ngày bắt đầu chính thức <= ngày kết thúc thử việc trong profile)
     *                   </li>
     *                  <li>Hợp đồng chọn đồng bộ loại khác:
     *                      <br/>-  Hệ thống không xử lý
     *                  </li>
     *               </ol>
     *       </li>
     * </ul>
     * @param ContractModel $contractModel
     * @throws Exception
     */
    public function updateContract(ContractModel $contractModel)
    {
        $employeeOld = clone $this;
        if (!$this->id) {
            throw new Exception(trans('contract::message.Employee not found'));
        }
        $workModel = $this->getWorkInfo();
        $currentWorkingType = isset($workModel->contract_type) ? (int)$workModel->contract_type : 0;
        $newWorkingType = $contractModel->type;
        $oldTraitDate = $this->trial_date;
        $oldTraitEndDate = $this->trial_end_date;
        $oldOffcialDate = $this->offcial_date;
        $newStartAt = $contractModel->start_at;
        $newEndAt = $contractModel->end_at;
        switch ($currentWorkingType) {
            case getOptions::WORKING_BORROW:
            case getOptions::WORKING_INTERNSHIP:
                if ($newWorkingType == getOptions::WORKING_PROBATION) {
                    $this->trial_date = $newStartAt;
                    $this->trial_end_date = $newEndAt;
                    $this->offcial_date = Carbon::parse($newEndAt)->addDay(1)->toDateString();
                } elseif (in_array($newWorkingType, [getOptions::WORKING_UNLIMIT, getOptions::WORKING_OFFICIAL])) {
                    $this->offcial_date = $newStartAt;
                } else {
                }
                break;
            case getOptions::WORKING_PROBATION:
                #III. Hợp đồng hiện tại là thử việc
                if ($newWorkingType == getOptions::WORKING_PROBATION) {
                    $this->trial_end_date = $newEndAt;
                    if (trim($oldTraitDate) == '') {
                        $this->trial_date = $newStartAt;
                    } else {
                        if (strtotime($oldTraitDate) > strtotime($newStartAt)) {
                            $this->trial_date = $newStartAt;
                        }
                    }
                    $this->offcial_date = Carbon::parse($newEndAt)->addDay(1)->toDateString();
                } elseif (in_array($newWorkingType, [getOptions::WORKING_UNLIMIT, getOptions::WORKING_OFFICIAL])) {
                    if (trim($oldTraitEndDate) != '') {
                        if (strtotime($oldTraitEndDate) >= strtotime($newStartAt)) {
                            $this->trial_end_date = Carbon::parse($newStartAt)->addDay(-1)->toDateString();
                        }
                    } else {
                        $this->trial_end_date = Carbon::parse($newStartAt)->addDay(-1)->toDateString();
                    }
                    $this->offcial_date = $newStartAt;
                } else {
                }
                break;
            case getOptions::WORKING_PARTTIME:
                #IV. Hợp đồng hiện tại là thời vụ
                if ($newWorkingType == getOptions::WORKING_PROBATION) {
                    $this->trial_date = $newStartAt;
                    $this->trial_end_date = $newEndAt;
                    $this->offcial_date = Carbon::parse($newEndAt)->addDay(1)->toDateString();
                } elseif (in_array($newWorkingType, [getOptions::WORKING_UNLIMIT, getOptions::WORKING_OFFICIAL])) {
                    if (trim($oldTraitEndDate) != '') {
                        if (strtotime($oldTraitEndDate) >= strtotime($newStartAt)) {
                            $this->trial_end_date = Carbon::parse($newStartAt)->addDay(-1)->toDateString();
                        }
                    }
                    $this->offcial_date = $newStartAt;
                } else {
                }
                break;
            case getOptions::WORKING_UNLIMIT:
            case getOptions::WORKING_OFFICIAL:
                #V. Hợp đồng hiện tại chính thức có hạn hoặc không có thời hạn
                if (in_array($newWorkingType, [getOptions::WORKING_UNLIMIT, getOptions::WORKING_OFFICIAL])) {
                    if (trim($oldTraitEndDate) != '' && strtotime($oldTraitEndDate) >= strtotime($newStartAt)) {
                        $this->trial_end_date = Carbon::parse($newStartAt)->addDay(-1)->toDateString();
                    }
                    if (trim($oldOffcialDate) != '') {
                        if (strtotime($oldOffcialDate) > strtotime($newStartAt)) {
                            $this->offcial_date = $newStartAt;
                        }
                    } else {
                        $this->offcial_date = $newStartAt;
                    }
                }
                break;
            default:
                #is contract first
                if (in_array($contractModel->type, [getOptions::WORKING_UNLIMIT, getOptions::WORKING_OFFICIAL])) {
                    $this->offcial_date = $contractModel->start_at;
                } elseif ($contractModel->type == getOptions::WORKING_PROBATION) {
                    $this->trial_date = $contractModel->start_at;
                    $this->trial_end_date = $newEndAt;
                    $this->offcial_date = Carbon::parse($newEndAt)->addDay(1)->toDateString();
                } else {
                    //todo
                }
                break;
        }
        $this->update(['trial_date', 'trial_end_date', 'offcial_date']);
        //Upload loai hop dong hien tai
        $this->updateWorkingType($contractModel);
        $this->synchrozineContractLog($employeeOld->toArray(), $this->toArray());
    }

    /**
     * Update working type
     * @param ContractModel $contractModel
     */
    public function updateWorkingType(ContractModel $contractModel)
    {
        //Đã bỏ không dùng trường working_type trong bảng employees
        // $this->working_type = $contractModel->type;
        // $this->update(['working_type']);
        $workModel = $this->getWorkInfo();
        if ($workModel) {
            $workModel->contract_type = $contractModel->type;
            $workModel->update(['contract_type']);
        } else {
            $workModel->employee_id = $this->id;
            $workModel->contract_type = $contractModel->type;
            $workModel->save();
        }
    }

    private function synchrozineContractLog(array $old, array $new)
    {
        $synchronizeLog = new SynchronizeLog();
        $synchronizeLog->employee_id = Auth::id();
        $synchronizeLog->employee_old = serialize($old);
        $synchronizeLog->employee_new = serialize($new);
        $synchronizeLog->save();
    }
}
