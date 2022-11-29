<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreConfigData;

class AssumptionConstrain extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_assumptions';

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
    protected $fillable = ['project_id', 'content', 'note',
                            'status', 'type', 'state', 'impact', 'action',
                            'start_at', 'type'];

    /**
     * Get the assumption constrain child
     */
    public function projectAssumptionConstrainChild() {
        return $this->hasOne('Rikkei\Project\Model\AssumptionConstrain', 'parent_id');
    }

    /*
     * get all assumption constrain by project id
     * @param int
     * @return collection
     */
    public static function getAllAssumptionConstrain($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'content', 'note', 'status', 'impact', 'action'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.assumption_constrain')) {
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
     * add assumption constrain
     * @param array
     */
    public static function insertAssumptionConstrain($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_ASSUMPTION_CONSTRAINS];
            if (config('project.workorder_approved.assumption_constrain')) {
                if (isset($input['isAddNew'])) {
                    $assumptionConstrains = new AssumptionConstrain();
                    $assumptionConstrains->project_id = $input['project_id'];
                    $assumptionConstrains->status = self::STATUS_DRAFT;
                    $assumptionConstrains->content = $input['content_1'];
                    $assumptionConstrains->note = $input['note_1'];
                    $assumptionConstrains->impact = $input['impact_1'];
                    $assumptionConstrains->action = $input['action_1'];
                    $assumptionConstrains->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $assumptionConstrains->save();
                    //Get old critical dependency member assignee
                    $memberOld = self::getAllMemberId($assumptionConstrains->id);
                    if ($memberOld) {
                        //Delete old training member
                        $assumptionConstrains->assumptionContrainMember()->detach($memberOld);
                    }
                    if ($input['member_1']) {
                        $criticalDependencies->assumptionContrainMember()->attach($input['member_1']);
                    }
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $assumptionConstrains = new AssumptionConstrain();
                        $assumptionConstrains->project_id = $input['project_id'];
                        $assumptionConstrains->status = self::STATUS_DRAFT_EDIT;
                        $assumptionConstrains->parent_id = $input['id'];
                        $assumptionConstrains->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $assumptionConstrains = self::find($input['id']);
                        if ($assumptionConstrains->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $assumptionConstrains->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($assumptionConstrains->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $assumptionConstrains->status = self::STATUS_DRAFT;
                        }
                        if ($assumptionConstrains->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($assumptionConstrains->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $assumptionConstrains->content = $input['content_1'];
                    $assumptionConstrains->note = $input['note_1'];
                    $assumptionConstrains->impact = $input['impact_1'];
                    $assumptionConstrains->action = $input['action_1'];
                    $assumptionConstrainsAttributes = $assumptionConstrains->attributes;
                    $assumptionConstrainsOrigin = $assumptionConstrains->original;
                    $assumptionConstrains->save();
                    //Get old critical dependency member assignee
                    $memberOld = self::getAllMemberId($assumptionConstrains->id);
                    if ($memberOld) {
                        //Delete old training member
                        $assumptionConstrains->assumptionContrainMember()->detach($memberOld);
                    }
                    if ($input['member_1']) {
                        $criticalDependencies->assumptionContrainMember()->attach($input['member_1']);
                    }
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $assumptionConstrainsAttributes, $assumptionConstrainsOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $assumptionConstrains = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $assumptionConstrains = new AssumptionConstrain;
                    $assumptionConstrains->project_id = $input['project_id'];
                    $assumptionConstrains->created_by = Permission::getInstance()->getEmployee()->id;
                    $assumptionConstrains->status = self::STATUS_APPROVED;
                }
                $assumptionConstrains->content = $input['content_1'];
                $assumptionConstrains->note = $input['note_1'];
                $assumptionConstrains->impact = $input['impact_1'];
                $assumptionConstrains->action = $input['action_1'];
                $assumptionConstrainsAttributes = $assumptionConstrains->attributes;
                $assumptionConstrainsOrigin = $assumptionConstrains->original;
                $assumptionConstrains->save();
                //Get old critical dependency member assignee
                $memberOld = self::getAllMemberId($assumptionConstrains->id);
                if ($memberOld) {
                    //Delete old training member
                    $assumptionConstrains->assumptionContrainMember()->detach($memberOld);
                }
                if ($input['member_1']) {
                    $assumptionConstrains->assumptionContrainMember()->attach($input['member_1']);
                }

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $assumptionConstrainsAttributes, $assumptionConstrainsOrigin);
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
     * delete assumption constrain
     * @param array
     * @return boolean
     */
    public static function deleteAssumptionConstrain($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_ASSUMPTION_CONSTRAINS];
        if (config('project.workorder_approved.assumption_constrain')) {
            $assumptionConstrains = self::find($input['id']);
            if ($assumptionConstrains) {
                if($assumptionConstrains->status == self::STATUS_APPROVED) {
                    $assumptionConstrainsDelete = $assumptionConstrains->replicate();
                    $assumptionConstrainsDelete->status = self::STATUS_DRAFT_DELETE;
                    $assumptionConstrainsDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($assumptionConstrainsDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if ($assumptionConstrains->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($assumptionConstrains->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($assumptionConstrains->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($assumptionConstrains->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($assumptionConstrains->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($assumptionConstrains->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }  else if ($assumptionConstrains->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($assumptionConstrains->delete()) {
                        $statusText = $arrayLablelStatus[$status];
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $assumptionConstrains = self::find($input['id']);
            if ($assumptionConstrains) {
                foreach ($assumptionConstrains->assumptionContrainMember as $key => $member) {
                    $assumptionConstrains->assumptionContrainMember()->detach($member);
                }
                $assumptionConstrains->push();
                if ($assumptionConstrains->delete()) {
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
        $assumptionDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $assumptionDraft = $assumptionDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $assumptionDraft = $assumptionDraft->where('status', self::STATUS_DRAFT);
        }
        $assumptionDraft = $assumptionDraft->get();

        if(count($assumptionDraft) > 0) {
            $title = Lang::get('project::view.Add object for Assumption and Constrain');
            $content .= view('project::template.content-task', ['inputs' => $assumptionDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $assumptionDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $assumptionDraftEdit = $assumptionDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $assumptionDraftEdit = $assumptionDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $assumptionDraftEdit = $assumptionDraftEdit->get();                        
        if(count($assumptionDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Assumption and Constrain');
            $content .= view('project::template.content-task', ['inputs' => $assumptionDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $assumptionDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $assumptionDelete = $assumptionDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $assumptionDelete = $assumptionDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $assumptionDelete = $assumptionDelete->get();
        if(count($assumptionDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Assumption and Constrain');
            $content .= view('project::template.content-task', ['inputs' => $assumptionDelete, 'title' => $title, 'type' => $typeWO])->render();
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
        $assumptionDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($assumptionDraft) > 0) {
            foreach($assumptionDraft as $assumption) {
                $assumption->status = self::STATUS_SUBMITTED;
                $assumption->task_id = $task->id;
                $assumption->save();
            }
        }

        $assumptionEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($assumptionEdit) > 0) {
            foreach($assumptionEdit as $assumption) {
                $assumption->status = self::STATUS_SUBMIITED_EDIT;
                $assumption->task_id = $task->id;
                $assumption->save();
            }
        }

        $assumptionDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($assumptionDelete)) {
            foreach($assumptionDelete as $assumption) {
                $assumption->status = self::STATUS_SUBMMITED_DELETE;
                $assumption->task_id = $task->id;
                $assumption->save();
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
        $assumptionDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $assumptionDraft = $assumptionDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $assumptionDraft = $assumptionDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $assumptionDraft = $assumptionDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $assumptionDraft = $assumptionDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $assumptionDraft = $assumptionDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $assumptionDraft = $assumptionDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $assumptionDraft = $assumptionDraft->get();
        if(count($assumptionDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($assumptionDraft as $assumption) {
                        $assumptionParent = self::find($assumption->parent_id);
                        $assumption->delete();
                        if($assumptionParent) {
                            $assumptionParent->delete();
                        }
                    }
                } else {
                    foreach($assumptionDraft as $assumption) {
                        $assumptionParent = self::find($assumption->parent_id);
                        if($assumptionParent) {
                            $assumption->parent_id = null;
                            $assumption->task_id = null;
                            $assumption->save();
                            $assumptionParent->delete();
                        }
                        $assumption->status = self::STATUS_APPROVED;
                        $assumption->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($assumptionDraft as $assumption) {
                    if ($assumption->status == self::STATUS_SUBMITTED) {
                        $assumption->status = self::STATUS_REVIEWED;
                    }
                    if ($assumption->status == self::STATUS_SUBMIITED_EDIT) {
                        $assumption->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($assumption->status == self::STATUS_SUBMMITED_DELETE) {
                        $assumption->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $assumption->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($assumptionDraft as $assumption) {
                    if ($assumption->status == self::STATUS_SUBMITTED ||
                        $assumption->status == self::STATUS_REVIEWED) {
                        $assumption->status = self::STATUS_FEEDBACK;
                    }
                    if ($assumption->status == self::STATUS_SUBMIITED_EDIT ||
                        $assumption->status == self::STATUS_REVIEWED_EDIT) {
                        $assumption->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($assumption->status == self::STATUS_SUBMMITED_DELETE ||
                        $assumption->status == self::STATUS_REVIEWED_DELETE) {
                        $assumption->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $assumption->save();
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
        $allassumptionConstrains = self::getAllAssumptionConstrain($project->id);
        return view('project::components.assumption-constrains', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allassumptionConstrains' => $allassumptionConstrains, 'detail' => true])->render();
    }
    
    /**
     * The employee that belong to Critical Dependencies
     */
    public function assumptionContrainMember() {
        $tableAssumptionAssignee = AssumptionsAssignee::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Employee', $tableAssumptionAssignee, 'assumption_id', 'employee_id');
    }
    
    /*
     * Get all member of AssumptionConstrain
     * @parram int
     * @return array
     */
    public static function getAllMemberOfAssumptionConstrain($id) 
    {
        $assumptionContrain = self::find($id);
        if (!$assumptionContrain) {
            return;
        }
        $members = array();
        foreach ($assumptionContrain->assumptionContrainMember as $member) {
            array_push($members, array('id' => $member->id, 'name'=> $member->name, 'email' => $member->email));
        }
        return $members;
    }
    
    /*
     * Get all member'id of Assumption Contrains
     * @parram int
     * @return array
     */
    public static function getAllMemberId($id) 
    {
        $assumptionContrain = self::find($id);
        if (!$assumptionContrain) {
            return;
        }
        $members = array();
        foreach ($assumptionContrain->assumptionContrainMember as $member) {
            array_push($members, $member->id);
        }
        return $members;
    }
    
    /**
     * get value attribute
     * @param int $id, int $attribute
     * @return string $attribute
     */
    public static function getValueAttribute($id, $attribute) {
        $value = self::find($id);
        if($value) {
            return $value->$attribute;
        }
        return null;
    }
}