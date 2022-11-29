<?php

namespace Rikkei\Contract\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Rikkei\Core\Model\CoreModel;
use Rikkei\HomeMessage\Helper\Constant;

class ContractConfirmExpire extends CoreModel
{
    protected $table = 'contract_confirm_expire';

    const NO_CONFIRM_CONTRACT = 2;
    const EXTEND_CONTRACT = 3;
    const END_CONTRACT = 4;

    /*
     * The attributes that are mass assignable.
     */

    protected $primaryKey = 'id';

    protected $fillable = [
        'contract_id',
        'type',
        'note',
    ];

    /**
     * @return array
     */
    public function getAllLabelType()
    {
        return [
            self::NO_CONFIRM_CONTRACT => trans('contract::view.No confirm yet'),
            self::EXTEND_CONTRACT => trans('contract::view.Agree to extend'),
            self::END_CONTRACT => trans('contract::view.End contract'),
        ];
    }

    /**
     * set background of label type
     * @return array
     */
    public function getbgText()
    {
        return [
            self::NO_CONFIRM_CONTRACT => 'label-warning',
            self::EXTEND_CONTRACT => 'label-primary',
            self::END_CONTRACT => 'label-danger',
        ];
    }

    /**
     * get confirm of employee by contactId
     *
     * @param $id
     * @return mixed
     */
    public function getConfirmByContactId($id)
    {
        $tblConfirm = self::getTableName();
        $tblContract = ContractModel::getTableName();

       return self::select(
           "{$tblConfirm}.id",
           "{$tblConfirm}.type",
           "{$tblConfirm}.note"
       )
       ->join("{$tblContract}", "{$tblContract}.id", '=', "{$tblConfirm}.contract_id")
       ->where("{$tblConfirm}.contract_id", $id)
       ->where("{$tblContract}.employee_id", Auth()->id())
       ->whereDate("{$tblContract}.end_at", '>=', Carbon::now()->format('Y-m-d'))
       ->whereNull("{$tblContract}.deleted_at")
       ->first();
    }
}