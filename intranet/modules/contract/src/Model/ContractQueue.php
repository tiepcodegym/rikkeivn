<?php

namespace Rikkei\Contract\Model;

use Carbon\Carbon;
use DB;
use Exception;
use Rikkei\Contract\Model\ContractConfirmExpire;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;

class ContractQueue extends ContractModel
{

    public static function notifyContractExpireDate()
    {
        $collection = ContractModel::select('*');
        ContractModel::findWhereExpireDate($collection,'end_at');
        $contracts = $collection->get();
        $leaver = Employee::getLeaverIds();
        if (!$contracts || $contracts->count() == 0) {
            Log::info('End job notifyContractExpireDate Not contract');
            return;
        }
        $dataInsert = [];
        $date = Carbon::now();
        foreach ($contracts as $contract) {
            if (in_array($contract->employee_id, $leaver)) continue;
            $dataInsert[] = [
                'contract_id' => $contract->id,
                'type' => ContractConfirmExpire::NO_CONFIRM_CONTRACT,
                'created_at' => $date,
                'updated_at' => $date,
            ];
            //========= send email cho nhân viên sắp hết hạn ==============
            $mailQueue = new EmailQueue();
            $mailQueue->setTo($contract->employee->email)
                ->setSubject(trans('contract::view.[Rikkeisofft] Employee contract notice has expired'))
                ->setTemplate('contract::emails.notify-expire-date', [
                    'employee' => $contract->employee->toArray(),
                    'contract' => $contract->toArray(),
                    'expireDate'=> $contract->end_at,
                    'link'=> route('contract::contract.list')
                ])
                ->save();
            //=======================
            $sentTo = $contract->getAllEmployeeReceiveNotify();
            if (count($sentTo) == 0) {
                Log::error("[Hợp đồng : [{$contract->id}] Không tìm thấy email người nhận thông báo hoặc thông tin nhân tin team quản lý hồ sơ");
                continue;
            }
            DB::beginTransaction();
                try {
                    foreach ($sentTo as $sentEmail) {
                        $mailQueue = new EmailQueue();
                        $mailQueue->setTo($sentEmail)
                                ->setSubject(trans('contract::view.[Rikkeisofft] Employee contract notice [:employee_name - :employee_email] has expired' , [
                                            'employee_name' => $contract->employee->name,
                                            'employee_email' => $contract->employee->email,
                                ]))
                                ->setTemplate('contract::emails.notify-expire-date', [
                                    'employee' => $contract->employee->toArray(),
                                    'contract' => $contract->toArray(),
                                    'expireDate'=> $contract->end_at
                                ])
                                ->save();
                    }
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollback();
                    Log::error($ex->getTraceAsString());
                    throw $ex;
                }
        }
        if ($dataInsert) {
            ContractConfirmExpire::insert($dataInsert);
        }
        Log::info('End job notifyContractExpireDate');
    }
}
