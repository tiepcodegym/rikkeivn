<?php

namespace Rikkei\Welfare\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Welfare\Model\WelfareFee;

class WelFeeMore extends CoreModel
{
    use SoftDeletes;

    protected $table = 'wel_fee_more';

    protected $fillable = ['wel_id', 'name', 'source', 'cost', 'created_by'];

    /**
     * get grid data
     *
     * @return object
     */
    public static function getGridData($id)
    {
        return self::select('name','source', 'cost', 'id', 'wel_id')
                ->where('wel_fee_more.wel_id', $id);
    }

    /**
     * The total additional cost of the welfare
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function totalFeeWelfare($id)
    {
        $total =  self::select(DB::raw('SUM(cost) as total'))
            ->where('wel_id', $id)
            ->first();
        return (int) $total->total;
    }

    /**
     *
     * @param int $welId
     * @param int $cost
     * @param boolean $update
     * @return boolean
     * @throws \Rikkei\Welfare\Model\Exception
     */
    public static function updateFeeActual($welId, $cost, $update = true)
    {
        $fee       = WelfareFee::where('wel_id', $welId)->first();
        $feeActual = (int) $fee->fee_total_actual;

        if ($update) {
            $total = $feeActual + (int) $cost;
        } else {
            $total = $feeActual - (int) $cost;
        }

        try {
            DB::beginTransaction();
            WelfareFee::where('id', $fee->id)
                ->update(['fee_total_actual' => $total]);
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
            return false;
        }
    }

}