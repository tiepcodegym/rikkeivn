<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;

class ExternalInterface extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_externals';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'name', 'position',
                            'responsibilities', 'contact',
                            'status', 'type', 'state',
                            'start_at', 'type'];

    /**
     * Get the external interface child
     */
    public function projectExternalInterfaceChild() {
        return $this->hasOne('Rikkei\Project\Model\ExternalInterface', 'parent_id');
    }                        

    /*
     * get all external interface by project id
     * @param int
     * @return collection
     */
    public static function getAllExternalInterface($projectId)
    {
        $item = self::select(['id', 'name', 'position', 'responsibilities', 'contact', 'status'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.external_interface')) {
            $item = $item->whereNull('parent_id')
                        ->orderBy('updated_at', 'asc')
                        ->get();
        } else {
            $item = $item->orderBy('updated_at', 'asc')
                        ->get();
        }
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add external interface
     * @param array
     */
    public static function insertExternalInterface($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_EXTERNAL_INTERFACE];
            if (config('project.workorder_approved.external_interface')) {
                if (isset($input['isAddNew'])) {
                    $externalInterface = new ExternalInterface();
                    $externalInterface->project_id = $input['project_id'];
                    $externalInterface->status = self::STATUS_DRAFT;
                    $externalInterface->name = $input['name_1'];
                    $externalInterface->position = $input['position_1'];
                    $externalInterface->responsibilities = $input['responsibilities_1'];
                    $externalInterface->contact = $input['contact_1'];
                    $externalInterface->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $externalInterface->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $externalInterface = new ExternalInterface();
                        $externalInterface->project_id = $input['project_id'];
                        $externalInterface->status = self::STATUS_DRAFT_EDIT;
                        $externalInterface->parent_id = $input['id'];
                        $externalInterface->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $externalInterface = self::find($input['id']);
                        if ($externalInterface->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $externalInterface->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($externalInterface->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $externalInterface->status = self::STATUS_DRAFT;
                        }
                        if ($externalInterface->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($externalInterface->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $externalInterface->name = $input['name_1'];
                    $externalInterface->position = $input['position_1'];
                    $externalInterface->responsibilities = $input['responsibilities_1'];
                    $externalInterface->contact = $input['contact_1'];
                    $externalInterfaceAttributes = $externalInterface->attributes;
                    $externalInterfaceOrigin = $externalInterface->original;
                    $externalInterface->save();
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $externalInterfaceAttributes, $externalInterfaceOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $externalInterface = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $externalInterface = new ExternalInterface;
                    $externalInterface->project_id = $input['project_id'];
                    $externalInterface->created_by = Permission::getInstance()->getEmployee()->id;
                    $externalInterface->status = self::STATUS_APPROVED;
                }
                $externalInterface->name = $input['name_1'];
                $externalInterface->position = $input['position_1'];
                $externalInterface->responsibilities = $input['responsibilities_1'];
                $externalInterface->contact = $input['contact_1'];
                $externalInterfaceAttributes = $externalInterface->attributes;
                $externalInterfaceOrigin = $externalInterface->original;
                $externalInterface->save();

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $externalInterfaceAttributes, $externalInterfaceOrigin);
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
     * delete external interface
     * @param array
     * @return boolean
     */
    public static function deleteExternalInterface($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_EXTERNAL_INTERFACE];
        if (config('project.workorder_approved.external_interface')) {
            $externalInterface = self::find($input['id']);
            if ($externalInterface) {
                if($externalInterface->status == self::STATUS_APPROVED) {
                    $externalInterfaceDelete = $externalInterface->replicate();
                    $externalInterfaceDelete->status = self::STATUS_DRAFT_DELETE;
                    $externalInterfaceDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($externalInterfaceDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if ($externalInterface->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($externalInterface->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($externalInterface->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($externalInterface->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($externalInterface->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($externalInterface->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($externalInterface->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $externalInterface = self::find($input['id']);
            if ($externalInterface) {
                if ($externalInterface->delete()) {
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
     * get content task approved
     * @param int
     * @param array
     * @return string
     *
     */
    public static function getContentTaskApproved($typeWO, $input, $content, $typeSubmit = null)
    {
        $externalInterfaceDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $externalInterfaceDraft = $externalInterfaceDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $externalInterfaceDraft = $externalInterfaceDraft->where('status', self::STATUS_DRAFT);
        }
        $externalInterfaceDraft = $externalInterfaceDraft->get();
        if(count($externalInterfaceDraft) > 0) {
            $title = Lang::get('project::view.Add object for External Interface');
            $content .= view('project::template.content-task', ['inputs' => $externalInterfaceDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $externalInterfaceDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $externalInterfaceDraftEdit = $externalInterfaceDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $externalInterfaceDraftEdit = $externalInterfaceDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $externalInterfaceDraftEdit = $externalInterfaceDraftEdit->get();
        if(count($externalInterfaceDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for External Interface');
            $content .= view('project::template.content-task', ['inputs' => $externalInterfaceDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $externalInterfaceDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $externalInterfaceDelete = $externalInterfaceDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $externalInterfaceDelete = $externalInterfaceDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $externalInterfaceDelete = $externalInterfaceDelete->get();
        if(count($externalInterfaceDelete) > 0) {
            $title = Lang::get('project::view.Delete object for External Interface');
            $content .= view('project::template.content-task', ['inputs' => $externalInterfaceDelete, 'title' => $title, 'type' => $typeWO])->render();
        }
        return $content; 
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

    /**
     * update status when submit workorder
     * @param array
     */
    public static function updateStatusWhenSubmitWorkorder($task, $input)
    {
        $externalDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($externalDraft) > 0) {
            foreach($externalDraft as $external) {
                $external->status = self::STATUS_SUBMITTED;
                $external->task_id = $task->id;
                $external->save();
            }
        }

        $externalEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($externalEdit) > 0) {
            foreach($externalEdit as $external) {
                $external->status = self::STATUS_SUBMIITED_EDIT;
                $external->task_id = $task->id;
                $external->save();
            }
        }

        $externalDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($externalDelete)) {
            foreach($externalDelete as $external) {
                $external->status = self::STATUS_SUBMMITED_DELETE;
                $external->task_id = $task->id;
                $external->save();
            }
        }
        
        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
    }

    /**
     * update status when submit slove workorder
     * @param array
     */
    public static function updateStatusWhenSloveWorkorder($statusTask, $projectId)
    {   
        self::updateItemWorkorder(self::TYPE_ADD, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_EDIT, $statusTask, $projectId);
        self::updateItemWorkorder(self::TYPE_DELETE, $statusTask, $projectId);
        CacheHelper::forget(self::KEY_CACHE_WO, $projectId);
    }

    /**
     * update itemt workorder
     * @param int
     * @param int
     * @param int
     */
    public static function updateItemWorkorder($type, $statusTask, $projectId)
    {
        $externalInterfaceDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $externalInterfaceDraft = $externalInterfaceDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $externalInterfaceDraft = $externalInterfaceDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $externalInterfaceDraft = $externalInterfaceDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $externalInterfaceDraft = $externalInterfaceDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $externalInterfaceDraft = $externalInterfaceDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $externalInterfaceDraft = $externalInterfaceDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $externalInterfaceDraft = $externalInterfaceDraft->get();
        if(count($externalInterfaceDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($externalInterfaceDraft as $externalInterface) {
                        $externalInterfaceParent = self::find($externalInterface->parent_id);
                        $externalInterface->delete();
                        if($externalInterfaceParent) {
                            $externalInterfaceParent->delete();
                        }
                    }
                } else {
                    foreach($externalInterfaceDraft as $externalInterface) {
                        $externalInterfaceParent = self::find($externalInterface->parent_id);
                        if($externalInterfaceParent) {
                            $externalInterface->parent_id = null;
                            $externalInterface->task_id = null;
                            $externalInterface->save();
                            $externalInterfaceParent->delete();
                        }
                        $externalInterface->status = self::STATUS_APPROVED;
                        $externalInterface->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($externalInterfaceDraft as $externalInterface) {
                    if ($externalInterface->status == self::STATUS_SUBMITTED) {
                        $externalInterface->status = self::STATUS_REVIEWED;
                    }
                    if ($externalInterface->status == self::STATUS_SUBMIITED_EDIT) {
                        $externalInterface->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($externalInterface->status == self::STATUS_SUBMMITED_DELETE) {
                        $externalInterface->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $externalInterface->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($externalInterfaceDraft as $externalInterface) {
                    if ($externalInterface->status == self::STATUS_SUBMITTED ||
                        $externalInterface->status == self::STATUS_REVIEWED) {
                        $externalInterface->status = self::STATUS_FEEDBACK;
                    }
                    if ($externalInterface->status == self::STATUS_SUBMIITED_EDIT ||
                        $externalInterface->status == self::STATUS_REVIEWED_EDIT) {
                        $externalInterface->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($externalInterface->status == self::STATUS_SUBMMITED_DELETE ||
                        $externalInterface->status == self::STATUS_REVIEWED_DELETE) {
                        $externalInterface->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $externalInterface->save();
                }
            }
        }
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
        $allExternalInterfaces = self::getAllExternalInterface($project->id);
        return view('project::components.external-interface', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allExternalInterfaces' => $allExternalInterfaces, 'detail' => true])->render();
    }
}