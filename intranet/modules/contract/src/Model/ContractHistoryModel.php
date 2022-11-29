<?php

namespace Rikkei\Contract\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Contract\Model\ContractModel;

class ContractHistoryModel extends CoreModel
{

    protected $table = 'contract_histories';

    use SoftDeletes;
    /*
     * The attributes that are mass assignable.
     */

    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'employee_id',
        'type',
        'start_at',
        'end_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Add new contract history
     * @param ContractModel $contractModel
     * @return boolean
     */
    public static function saveHistory(ContractModel $contractModel)
    {
        $model = new self();
        $model->contract_id = $contractModel->id;
        $model->employee_id = $contractModel->employee_id;
        $model->type = $contractModel->type;
        $model->start_at = $contractModel->start_at;
        $model->end_at = $contractModel->end_at;
        $model->created_id = $contractModel->created_id;
        $model->deleted_at = $contractModel->deleted_at;
        return $model->save();
    }

}
