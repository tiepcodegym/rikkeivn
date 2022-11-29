<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\View\CacheHelper;
use Rikkei\Project\View\View;
use Rikkei\Team\View\Permission;
use DB;
use Carbon\Carbon;

class DevicesExpense extends ProjectWOBase
{
    protected $table = 'devices_expenses';

    protected $fillable = ['time', 'amount', 'description', 'project_id', 'status', 'parent_id', 'task_id', 'created_by'];


    /**
     * Get the tool and infrastructure child
     */
    public function projectDerivedExpensesChild() {
        return $this->hasOne('Rikkei\Project\Model\DevicesExpense', 'parent_id');
    }

    /**
     * get conten table after submit
     * @param array
     * @return string
     */
    public static function getContentTable($project)
    {
        $permission = View::checkPermissionEditWorkorder($project);
        $permissionEdit = $permission['persissionEditPM'] || $permission['permissionEditSubPM'] || $permission['permissionEditPqa'];
        $checkEditWorkOrder = Task::checkEditWorkOrder($project->id);
        $allDerivedExpense = self::getAllDerivedExpense($project->id);

        return view('project::components.devices-expenses', [
            'permissionEdit' => $permissionEdit,
            'checkEditWorkOrder' => $checkEditWorkOrder,
            'allDerivedExpense' => $allDerivedExpense,
            'detail' => true,
        ])->render();
    }

    /*
     * get all Derived Expense by project id
     * @param int
     * @return collection
     */
    public static function getAllDerivedExpense($projectId)
    {
        $item = self::select(['id', 'time', 'amount', 'description', 'project_id'])
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'asc')
            ->get();

        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /**
     * check status submit
     * @param int
     * @return boolean
     */
    public static function checkStatusSubmit($projectId)
    {
        $status = false;
        if ($checkStatus = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $checkStatus;
        }
        $items = self::where('project_id', $projectId)
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_DRAFT_EDIT, self::STATUS_DRAFT_DELETE])->count();
        if ($items > 0) {
            $status = true;
        }
        CacheHelper::put(self::KEY_CACHE_WO, $status, $projectId);
        return $status;
    }

    /*
    * add tool and infrastructure
    * @param array
    */
    public static function insertDerivedExpense($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_DEVICES_EXPENSE];
            if (config('project.workorder_approved.devices_expenses')) {
                if (isset($input['isAddNew'])) {
                    $derived = new DevicesExpense();
                    $derived->project_id = $input['project_id'];
                    $derived->status = self::STATUS_DRAFT;
                    $derived->time = Carbon::parse($input['time_1']);
                    $derived->amount = $input['amount_1'];
                    $derived->description = $input['description_1'];
                    $derived->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $derived->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $derived = new DevicesExpense();
                        $derived->project_id = $input['project_id'];
                        $derived->status = self::STATUS_DRAFT_EDIT;
                        $derived->parent_id = $input['id'];
                        $derived->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $derived = self::find($input['id']);
                        if ($derived->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $derived->status = self::STATUS_DRAFT_EDIT;
                        } else if ($derived->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $derived->status = self::STATUS_DRAFT;
                        } else if ($derived->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        } else if ($derived->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        } else {
                            // nothing
                        }
                    }
                    $derived->time = Carbon::parse( $input['time_1']);
                    $derived->amount = $input['amount_1'];
                    $derived->description = $input['description_1'];
                    $toolAttributes = $derived->attributes;
                    $toolOrigin = $derived->original;
                    $derived->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $toolAttributes, $toolOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $derived = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $derived = new DevicesExpense;
                    $derived->project_id = $input['project_id'];
                    $derived->created_by = Permission::getInstance()->getEmployee()->id;
                    $derived->status = self::STATUS_APPROVED;
                }
                $derived->time = Carbon::parse($input['time_1']);
                $derived->amount = $input['amount_1'];
                $derived->description = $input['description_1'];
                $toolAttributes = $derived->attributes;
                $toolOrigin = $derived->original;
                $derived->save();


                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $toolAttributes, $toolOrigin);
                }
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
            return true;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /*
     * delete Derived Expense
     * @param array
     * @return boolean
     */
    public static function deleteDerivedExpense($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_DEVICES_EXPENSE];
        if (config('project.workorder_approved.devices_expenses')) {
            $derived = self::find($input['id']);
            if ($derived) {
                if($derived->status == self::STATUS_APPROVED) {
                    $derivedDelete = $derived->replicate();
                    $derivedDelete->status = self::STATUS_DRAFT_DELETE;
                    $derivedDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($derivedDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if (array_key_exists($derived->status, self::arrStatus())) {
                        $status = self::arrStatus()[$derived->status];
                    }
                    if ($derived->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $derived = self::find($input['id']);
            if ($derived) {
                if ($derived->delete()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get status
     * @return array
     */
    private static function arrStatus()
    {
        return  [
            self::STATUS_DRAFT_EDIT => self::STATUS_DELETE_DRAFT_EDIT,
            self::STATUS_DRAFT => self::STATUS_DELETE_DRAFT,
            self::STATUS_FEEDBACK_DELETE =>  self::STATUS_FEEDBACK_DELETE,
            self::STATUS_FEEDBACK_EDIT =>  self::STATUS_DELETE_FEEDBACK_EDIT,
            self::STATUS_FEEDBACK =>  self::STATUS_DELETE_FEEDBACK,
            self::STATUS_DRAFT_DELETE =>  self::STATUS_DRAFT_DELETE
        ];
    }
}
