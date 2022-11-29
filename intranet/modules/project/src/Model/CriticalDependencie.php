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

class CriticalDependencie extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_criticals';

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
                            'status', 'type', 'state',
                            'start_at', 'type', 'impact', 'action'];


    /**
     * Get the critical dependencies child
     */
    public function projectCriticalDependenciesChild() {
        return $this->hasOne('Rikkei\Project\Model\CriticalDependencie', 'parent_id');
    }

    /*
     * get all critical dependencie by project id
     * @param int
     * @return collection
     */
    public static function getAllCriticalDependencie($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'content', 'expected_date'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.critical_dependencies')) {
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
     * add critical dependencie
     * @param array
     */
    public static function insertCriticalDependencie($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_CRITICAL_DEPENDENCIES];
            if (config('project.workorder_approved.critical_dependencies')) {
                if (isset($input['isAddNew'])) {
                    $criticalDependencies = new CriticalDependencie();
                    $criticalDependencies->project_id = $input['project_id'];
                    $criticalDependencies->status = self::STATUS_DRAFT;
                    $status = self::STATUS_DRAFT;
                    $criticalDependencies->content = $input['content_1'];
                    $criticalDependencies->expected_date = $input['expected_date_1'];
                    $criticalDependencies->created_by = Permission::getInstance()->getEmployee()->id;
                    $criticalDependencies->save();
                    //Get old critical dependency member assignee
                    $memberOld = self::getAllMemberId($criticalDependencies->id);
                    if ($memberOld) {
                        //Delete old training member
                        $criticalDependencies->criticalDependencyMember()->detach($memberOld);
                    }
                    if ($input['member_1']) {
                        $criticalDependencies->criticalDependencyMember()->attach($input['member_1']);
                    }
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $criticalDependencies = new CriticalDependencie();
                        $criticalDependencies->project_id = $input['project_id'];
                        $criticalDependencies->status = self::STATUS_DRAFT_EDIT;
                        $criticalDependencies->parent_id = $input['id'];
                        $criticalDependencies->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $criticalDependencies = self::find($input['id']);
                        if ($criticalDependencies->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $criticalDependencies->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($criticalDependencies->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $criticalDependencies->status = self::STATUS_DRAFT;
                        }
                        if ($criticalDependencies->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($criticalDependencies->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $criticalDependencies->content = $input['content_1'];
                    $criticalDependencies->expected_date = $input['expected_date_1'];
                    $criticalDependenciesAttributes = $criticalDependencies->attributes;
                    $criticalDependenciesOrigin = $criticalDependencies->original;
                    $criticalDependencies->save();
                    
                    //Get old critical dependency member assignee
                    $memberOld = self::getAllMemberId($criticalDependencies->id);
                    if ($memberOld) {
                        //Delete old training member
                        $criticalDependencies->criticalDependencyMember()->detach($memberOld);
                    }
                    if ($input['member_1']) {
                        $criticalDependencies->criticalDependencyMember()->attach($input['member_1']);
                    }
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $criticalDependenciesAttributes, $criticalDependenciesOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $criticalDependencies = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $criticalDependencies = new CriticalDependencie;
                    $criticalDependencies->project_id = $input['project_id'];
                    $criticalDependencies->created_by = Permission::getInstance()->getEmployee()->id;
                    $criticalDependencies->status = self::STATUS_APPROVED;
                }
                $criticalDependencies->content = $input['content_1'];
                $criticalDependencies->expected_date = $input['expected_date_1'];
                $criticalDependenciesAttributes = $criticalDependencies->attributes;
                $criticalDependenciesOrigin = $criticalDependencies->original;
                $criticalDependencies->save();
                
                //Get old critical dependency member assignee
                $memberOld = self::getAllMemberId($criticalDependencies->id);
                if ($memberOld) {
                    //Delete old training member
                    $criticalDependencies->criticalDependencyMember()->detach($memberOld);
                }
                if (isset($input['member_1'])) {
                    $criticalDependencies->criticalDependencyMember()->attach($input['member_1']);
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $criticalDependenciesAttributes, $criticalDependenciesOrigin);
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
     * delete critical dependencie
     * @param array
     * @return boolean
     */
    public static function deleteCriticalDependencie($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_CRITICAL_DEPENDENCIES];
        if (config('project.workorder_approved.critical_dependencies')) {
            $criticalDependencies = self::find($input['id']);
            if ($criticalDependencies) {
                if($criticalDependencies->status == self::STATUS_APPROVED) {
                    $criticalDelete = $criticalDependencies->replicate();
                    $criticalDelete->status = self::STATUS_DRAFT_DELETE;
                    $criticalDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($criticalDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if ($criticalDependencies->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($criticalDependencies->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($criticalDependencies->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($criticalDependencies->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($criticalDependencies->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($criticalDependencies->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($criticalDependencies->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $criticalDependencies = self::find($input['id']);
            if ($criticalDependencies) {
                foreach ($criticalDependencies->criticalDependencyMember as $key => $member) {
                    $criticalDependencies->criticalDependencyMember()->detach($member);
                }
                $criticalDependencies->push();
                if ($criticalDependencies->delete()) {
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
        $criticalDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $criticalDraft = $criticalDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $criticalDraft = $criticalDraft->where('status', self::STATUS_DRAFT);
        }
        $criticalDraft = $criticalDraft->get();
        if(count($criticalDraft) > 0) {
            $title = Lang::get('project::view.Add object for Critical dependencies');
            $content .= view('project::template.content-task', ['inputs' => $criticalDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $criticalDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $criticalDraftEdit = $criticalDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $criticalDraftEdit = $criticalDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $criticalDraftEdit = $criticalDraftEdit->get();
        if(count($criticalDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Critical dependencies');
            $content .= view('project::template.content-task', ['inputs' => $criticalDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $criticalDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $criticalDelete = $criticalDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $criticalDelete = $criticalDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $criticalDelete = $criticalDelete->get();
        if(count($criticalDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Critical dependencies');
            $content .= view('project::template.content-task', ['inputs' => $criticalDelete, 'title' => $title, 'type' => $typeWO])->render();
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
        $criticalDependenciesDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($criticalDependenciesDraft) > 0) {
            foreach($criticalDependenciesDraft as $criticalDependencies) {
                $criticalDependencies->status = self::STATUS_SUBMITTED;
                $criticalDependencies->task_id = $task->id;
                $criticalDependencies->save();
            }
        }

        $criticalDependenciesEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($criticalDependenciesEdit) > 0) {
            foreach($criticalDependenciesEdit as $criticalDependencies) {
                $criticalDependencies->status = self::STATUS_SUBMIITED_EDIT;
                $criticalDependencies->task_id = $task->id;
                $criticalDependencies->save();
            }
        }

        $criticalDependenciesDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($criticalDependenciesDelete)) {
            foreach($criticalDependenciesDelete as $criticalDependencies) {
                $criticalDependencies->status = self::STATUS_SUBMMITED_DELETE;
                $criticalDependencies->task_id = $task->id;
                $criticalDependencies->save();
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
        $criticalDependenciesDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $criticalDependenciesDraft = $criticalDependenciesDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $criticalDependenciesDraft = $criticalDependenciesDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $criticalDependenciesDraft = $criticalDependenciesDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $criticalDependenciesDraft = $criticalDependenciesDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $criticalDependenciesDraft = $criticalDependenciesDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $criticalDependenciesDraft = $criticalDependenciesDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $criticalDependenciesDraft = $criticalDependenciesDraft->get();
        if(count($criticalDependenciesDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($criticalDependenciesDraft as $criticalDependencies) {
                        $criticalDependenciesParent = self::find($criticalDependencies->parent_id);
                        $criticalDependencies->delete();
                        if($criticalDependenciesParent) {
                            $criticalDependenciesParent->delete();
                        }
                    }
                } else {
                    foreach($criticalDependenciesDraft as $criticalDependencies) {
                        $criticalDependenciesParent = self::find($criticalDependencies->parent_id);
                        if($criticalDependenciesParent) {
                            $criticalDependencies->parent_id = null;
                            $criticalDependencies->task_id = null;
                            $criticalDependencies->save();
                            $criticalDependenciesParent->delete();
                        }
                        $criticalDependencies->status = self::STATUS_APPROVED;
                        $criticalDependencies->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($criticalDependenciesDraft as $criticalDependencies) {
                    if ($criticalDependencies->status == self::STATUS_SUBMITTED) {
                        $criticalDependencies->status = self::STATUS_REVIEWED;
                    }
                    if ($criticalDependencies->status == self::STATUS_SUBMIITED_EDIT) {
                        $criticalDependencies->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($criticalDependencies->status == self::STATUS_SUBMMITED_DELETE) {
                        $criticalDependencies->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $criticalDependencies->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($criticalDependenciesDraft as $criticalDependencies) {
                    if ($criticalDependencies->status == self::STATUS_SUBMITTED ||
                        $criticalDependencies->status == self::STATUS_REVIEWED) {
                        $criticalDependencies->status = self::STATUS_FEEDBACK;
                    }
                    if ($criticalDependencies->status == self::STATUS_SUBMIITED_EDIT ||
                        $criticalDependencies->status == self::STATUS_REVIEWED_EDIT) {
                        $criticalDependencies->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($criticalDependencies->status == self::STATUS_SUBMMITED_DELETE ||
                        $criticalDependencies->status == self::STATUS_REVIEWED_DELETE) {
                        $criticalDependencies->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $criticalDependencies->save();
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
        $allCriticalDependencies = self::getAllCriticalDependencie($project->id);
        return view('project::components.critical-dependencies', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allCriticalDependencies' => $allCriticalDependencies, 'detail' => true])->render();
    }
    
    /**
     * The employee that belong to Critical Dependencies
     */
    public function criticalDependencyMember() {
        $tableCriticalAssignee = CriticalsAssignee::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Employee', $tableCriticalAssignee, 'critical_id', 'employee_id');
    }
    
    /*
     * Get all member of Critical Dependency
     * @parram int
     * @return array
     */
    public static function getAllMemberOfCriticalDependency($id) 
    {
        $criticalDependency = self::find($id);
        if (!$criticalDependency) {
            return;
        }
        $members = array();
        foreach ($criticalDependency->criticalDependencyMember as $member) {
            array_push($members, array('id' => $member->id, 'name'=> $member->name, 'email' => $member->email));
        }
        return $members;
    }
    
    /*
     * Get all member'id of Critical Dependency
     * @parram int
     * @return array
     */
    public static function getAllMemberId($id) 
    {
        $criticalDependency = self::find($id);
        if (!$criticalDependency) {
            return;
        }
        $members = array();
        foreach ($criticalDependency->criticalDependencyMember as $member) {
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
            return  nl2br($value->$attribute);
        }
        return null;
    }
}