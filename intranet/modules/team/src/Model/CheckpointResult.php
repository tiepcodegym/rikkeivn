<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\View\CacheHelper;
use DB;
use Rikkei\Team\Model\CheckpointResultDetail;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;

class CheckpointResult extends \Rikkei\Core\Model\CoreModel
{
    protected $table = 'checkpoint_result';

    /*
     * key store cache
     */
    const KEY_CACHE = 'checkpoint_result';
    const KEY_CACHE_CHECKPOINT_ID = 'checkpoint_result_checkpoint_id';

    /**
     * Insert into table checkpoint_result
     *
     * @param array $data
     * @param CheckpointResult $result
     * @return int
     */
    public function saveData($data, $arrayQuestion, $result = null)
    {
        DB::beginTransaction();
        try {
            $insert = false;
            if (!$result) {
                $result = new CheckpointResult();
                $result->updated_at = null;
                $insert = true;
            }
            $result->checkpoint_id = $data['checkpoint_id'];
            $result->total_point = $data['total_point'];
            $result->comment = $data['comment'];
            $result->employee_id = $data['employee_id'];
            $result->team_id = $data['team_id'];
            $result->save();

            //Save detail
            $detailModel = new CheckpointResultDetail();
            if ($insert) {
                $detailModel->insertData($result->id, $arrayQuestion);
            } else {
                $detailModel->updateDetail($result->id, $arrayQuestion, true);
            }
            DB::commit();
            return $result->id;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Get Checkpoint by checkpoint_id
     * @param int $id checkpoint_id
     * @return Checkpoint
     */
    public static function getResultById($id)
    {
        if ($result = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $result;
        }
        $result = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $result, $id);
        return $result;
    }

    /**
     * Update checkpoint to database
     */
    public function updateResult()
    {
        DB::beginTransaction();
        try {
            $this->save();
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE, $this->id);
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Get count 
     *
     * @param int $checkpointId
     * @param int $empId
     */
    public static function checkMade($checkpointId, $empId)
    {
        return self::where(
                    [
                        'checkpoint_id' => $checkpointId,
                        'employee_id'   => $empId
                    ]
                )->count();
        
    }

    public static function getOnlyResult($checkpointId)
    {
        if ($result = CacheHelper::get(self::KEY_CACHE_CHECKPOINT_ID, $checkpointId)) {
            return $result;
        }
        $result = self::where('checkpoint_id', $checkpointId)->first();
        CacheHelper::put(self::KEY_CACHE_CHECKPOINT_ID, $result, $checkpointId);
        return $result;
    }

    /**
     * Display checkpoint result list 
     * @param int $checkpointId
     * @param string $order
     * @param string $dir
     * @param int $evaluatorId
     * @return object list css result
     */
    public static function getResultByCheckpointId($checkpointId, $order, $dir, $filter)
    {
        $tableResult = self::getTableName();
        $empTbl = Employee::getTableName();
        $checkpoint = Checkpoint::getCheckpointById($checkpointId);

        $result = Employee::leftJoin(
                    DB::raw("(SELECT checkpoint_result.*, employees.id as emp_id "
                            . "FROM employees inner join checkpoint_result ON employees.id = checkpoint_result.employee_id "
                            . "WHERE checkpoint_result.checkpoint_id = $checkpointId "
                            . "GROUP BY checkpoint_result.id) AS tableResult")
                    , 'employees.id', '=', 'tableResult.emp_id')
                ->where(function ($query) use ($checkpoint) {
                    $query->whereRaw('employees.id in ('
                            . 'select employee_team_history.employee_id '
                            . 'from employee_team_history join employees on employee_team_history.employee_id = employees.id '
                            . 'WHERE (employees.leave_date is null or employees.leave_date > ?) '
                                . 'AND (employee_team_history.team_id = ? AND (DATE(employee_team_history.start_at) <= DATE(?) or employee_team_history.start_at is null) AND (DATE(employee_team_history.end_at) >= DATE(?) or employee_team_history.end_at is null)))', 
                        [$checkpoint->start_date, $checkpoint->team_id, $checkpoint->end_date, $checkpoint->start_date])
                    ->orWhereRaw('employees.id in (select checkpoint_result.employee_id from checkpoint_result where checkpoint_result.checkpoint_id = ? AND checkpoint_result.team_id =?)', [$checkpoint->id, $checkpoint->team_id]);
                })
                ->select('tableResult.*', 'employees.name as emp_name', 'employees.id as emp_id', 'employees.leave_date');

        //Filter
        if (!empty($filter['checkpoint_result.create_at'])) {
            if ($filter['checkpoint_result.create_at'] == Checkpoint::NOT_CREATED) {
                $result->whereRaw("{$empTbl}.id NOT IN (SELECT employee_id FROM {$tableResult} WHERE checkpoint_id = ?)", [$checkpointId]);
                $evaluateds = Checkpoint::where('id', $checkpointId)->pluck('evaluated_id')->first();
                $result->whereIn("{$empTbl}.id", explode(',', $evaluateds));
            } else{
               $result->whereRaw("{$empTbl}.id IN (SELECT employee_id FROM {$tableResult} WHERE checkpoint_id = ?)", [$checkpointId]);
               } 
            
        }
        
        if(!empty($filter['checkpoint_result.update_at'])){
            if($filter['checkpoint_result.update_at']== Checkpoint::NOT_CREATED){
                $result->whereRaw("{$empTbl}.id NOT IN (SELECT employee_id FROM {$tableResult} WHERE updated_at IS NOT NULL AND checkpoint_id = ?)", [$checkpointId]);
               $evaluateds = Checkpoint::where('id', $checkpointId)->pluck('evaluated_id')->first();
               $result->whereIn("{$empTbl}.id", explode(',', $evaluateds));
            }  else {
               $result->whereRaw("{$empTbl}.id IN (SELECT employee_id FROM {$tableResult} WHERE leader_total_point > 0.00 AND checkpoint_id = ?)", [$checkpointId]);
            }

        }


        CoreModel::filterGrid($result);
        if ($order != 'evaluator_id') {
            $result->orderBy($order, $dir);
        }

        return $result->get();
    }

    /**
     * Check employee made checkpoint. 
     * @param int $checkpointId
     * @param int $empId
     * @return int 
     */
    public static function checkExist($checkpointId, $empId)
    {
        return self::where('checkpoint_id', $checkpointId)
                      ->where('employee_id', $empId)
                      ->count();
    }

    /**
     * Get checkpoint result of employee
     * List result or result only of a checkpoint
     *
     * @param int $empId
     * @param int|null $checkpointId
     * @return CheckpointResult | CheckpointResult collection
     */
    public static function getResultOfEmployee($empId, $checkpointId = null)
    {
        $result = self::where('employee_id', $empId);
        if ($checkpointId) {
            $result->where('checkpoint_id', $checkpointId);
            return $result->first();
        }
        return $result->get();
    }
}
