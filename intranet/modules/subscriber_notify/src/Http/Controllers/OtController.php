<?php

namespace Rikkei\SubscriberNotify\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Rikkei\Ot\View\OtEmailManagement;
use Rikkei\Ot\Model\OtRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;

class OtController extends Controller
{
    const TYPE_REGISTER = 1; //insert or update
    const TYPE_APPROVED = 2;
    const TYPE_REJECTED = 3;

    /**
     * @param Request $request
     * @param int $employee_id
     * @param $type
     * @return bool
     */
    public function subscriber(Request $request, $employee_id, $type)
    {
        DB::beginTransaction();
        try {
            $employeeInfo = Employee::getEmpById($employee_id);
            if (!$employeeInfo) {
                throw new Exception('User send not found');
            }
            $id = $request->get('id');
            $registerRecord = OtRegister::getRegisterInfo($id);
            if (!$registerRecord) {
                throw new Exception('Ot not found');
            }
            switch (intval($type)) {
                case self::TYPE_REGISTER:
                    if ((int)$registerRecord->status != OtRegister::WAIT) {
                        throw new Exception('Param is invalid');
                    }
                    if ((int)$registerRecord->employee_id != ($employeeInfo->id)) {
                        throw new Exception('Param is invalid');
                    }
                    if ($employeeInfo->id != $registerRecord->appprover) {
                        $this->pushNotifyRegister($registerRecord,$employeeInfo);
                    }
                    break;
                case self::TYPE_APPROVED:
                    if ((int)$registerRecord->status != OtRegister::DONE) {
                        throw new Exception('Param is invalid');
                    }
                    if ((int)$registerRecord->approver != ($employeeInfo->id)) {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyApproved($registerRecord,$employeeInfo);
                    break;
                case self::TYPE_REJECTED:
                    if ((int)$registerRecord->status != OtRegister::REJECT) {
                        throw new Exception('Param is invalid');
                    }
                    if ((int)$registerRecord->approver != ($employeeInfo->id)) {
                        throw new Exception('Param is invalid');
                    }
                    $this->pushNotifyRejected($registerRecord,$employeeInfo);
                    break;
                default:
                    throw new Exception('Param is invalid');
                    break;
            }
            DB::commit();
            return response()->json(['success' => 1, 'message' => 'success'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            return response()->json(['success' => 0, 'message' => $exception->getMessage()], 422);
        }
    }


    /**
     * @param OtRegister $registerRecord
     * @param OtRegister $OtRegister
     * @throws Exception
     */
    protected function pushNotifyRegister(OtRegister $registerRecord,$employeeInfo)
    {
        $template = 'ot::template.mail_register.mail_register_to_approver';
        OtEmailManagement::setEmailRegister($registerRecord, $template,$employeeInfo->id);
    }


    /**
     * @param Request $request
     * @param OtRegister $OtRegister
     * @return bool
     * @throws Exception
     */
    protected function pushNotifyApproved(OtRegister $registerRecord,$employeeInfo)
    {
        $template = 'ot::template.mail_approve.mail_approve_to_register';
        OtEmailManagement::setEmailApproverAction($registerRecord, $template, OtRegister::DONE,$employeeInfo->id);
    }


    /**
     * @param Request $request
     * @param OtRegister $OtRegister
     * @return bool
     * @throws Exception
     */
    protected function pushNotifyRejected(OtRegister $registerRecord,$employeeInfo)
    {
        $template = 'ot::template.mail_disapprove.mail_disapprove_to_register';
        OtEmailManagement::setEmailApproverAction($registerRecord, $template, OtRegister::REJECT,$employeeInfo->id);
    }
}
