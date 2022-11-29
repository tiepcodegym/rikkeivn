<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Rikkei\Project\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\CoreConfigData;

class Training extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_trainings';

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
    protected $fillable = ['project_id', 'topic', 'description',
                            'participants', 'end_at', 'walver_criteria',
                            'status', 'type', 'state',
                            'start_at', 'type'];
    
    /**
     * Get the traning plan child
     */
    public function projectTrainingChild() {
        return $this->hasOne('Rikkei\Project\Model\Training', 'parent_id');
    }

    /**
     * The employee that belong to traning plan
     */
    public function traningMember() {
        $tableTrainingMember = TrainingMember::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Employee', $tableTrainingMember, 'training_id', 'employee_id')->withTimestamps();
    }

    /*
     * get all training by project id
     * @param int
     * @return collection
     */
    public static function getAllTraining($projectId)
    {   
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'topic', 'description', 'participants', 'walver_criteria', 'status', 'start_at', 'end_at', 'result'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.training')) {
            $item = $item->whereNull('parent_id')
                        ->orderBy('created_at', 'asc')
                        ->get();
        } else {
            $item = $item->orderBy('created_at', 'asc')
                        ->get();
        }
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add training
     * @param array
     */
    public static function insertTraining($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_TRANING];
            if (config('project.workorder_approved.training')) {
                if (isset($input['isAddNew'])) {
                    $training = new Training();
                    $training->project_id = $input['project_id'];
                    $training->status = self::STATUS_DRAFT;
                    $training->topic = $input['topic_1'];
                    $training->description = $input['description_1'];
                    $training->start_at = $input['start_at_1'];
                    $training->end_at = $input['end_at_1'];
                    $training->result = $input['result'];
                    $training->walver_criteria = $input['walver_criteria_1'];
                    $training->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $training->save();

                    //Get old training member
                    $memberOld = self::getAllMemberOfTraining($training->id);
                    if ($memberOld) {
                        //Delete old training member
                        $training->traningMember()->detach($memberOld);
                    }
                    $training->traningMember()->attach($input['member_1']);
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $training = new Training();
                        $training->project_id = $input['project_id'];
                        $training->status = self::STATUS_DRAFT_EDIT;
                        $training->parent_id = $input['id'];
                        $training->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $training = self::find($input['id']);
                        if ($training->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $training->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($training->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $training->status = self::STATUS_DRAFT;
                        }
                        if ($training->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($training->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $training->topic = $input['topic_1'];
                    $training->description = $input['description_1'];
                    $training->start_at = $input['start_at_1'];
                    $training->end_at = $input['end_at_1'];
                    $training->result = $input['result'];
                    $training->walver_criteria = $input['walver_criteria_1'];
                    $trainingAttributes = $training->attributes;
                    $trainingOrigin = $training->original;
                    $training->save();

                    //Get old training member
                    $memberOld = self::getAllMemberOfTraining($training->id);
                    if ($memberOld) {
                        //Delete old training member
                        $training->traningMember()->detach($memberOld);
                    }
                    $training->traningMember()->attach($input['member_1']);
                }
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $trainingAttributes, $trainingOrigin);
                }
            } else {
                if (isset($input['id'])) {
                    $status = self::STATUS_EDIT;
                    $training = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $training = new Training;
                    $training->project_id = $input['project_id'];
                    $training->created_by = Permission::getInstance()->getEmployee()->id;
                    $training->status = self::STATUS_APPROVED;
                }
                $training->topic = $input['topic_1'];
                $training->description = $input['description_1'];
                $training->start_at = $input['start_at_1'];
                $training->end_at = $input['end_at_1'];
                $training->result = $input['result'];
                $training->walver_criteria = $input['walver_criteria_1'];
                $trainingAttributes = $training->attributes;
                $trainingOrigin = $training->original;
                $training->save();
                
                //Get old training member
                $memberOld = self::getAllMemberOfTraining($training->id);
                if ($memberOld) {
                    //Delete old training member
                    $training->traningMember()->detach($memberOld);
                }
                $training->traningMember()->attach($input['member_1']);
                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_ADD) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $trainingAttributes, $trainingOrigin);
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

    public static function getLabelStatusTraining($status)
    {
        if (!$status) {
            return false;
        }
        switch ($status) {
            case self::STATUS_RESULT_PASS:
                return trans('project::view.Pass');
            case self::STATUS_RESULT_FAIL:
                return trans('project::view.Fail');
        }
    }

    /*
     * delete training
     * @param array
     * @return boolean
     */
    public static function deleteTraining($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_TRANING];
        if (config('project.workorder_approved.training')) {
            $training = self::find($input['id']);
            if ($training) {
                if($training->status == self::STATUS_APPROVED) {
                    $trainingDelete = $training->replicate();
                    $trainingDelete->status = self::STATUS_DRAFT_DELETE;
                    $trainingDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($trainingDelete->save()) {
                        foreach ($training->traningMember as $key => $member) {
                            $trainingDelete->traningMember()->attach($member);
                        }
                        $trainingDelete->push();
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if ($training->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($training->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($training->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($training->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($training->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($training->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($training->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    } 
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $training = self::find($input['id']);
            if ($training) {
                foreach ($training->traningMember as $key => $member) {
                    $training->traningMember()->detach($member);
                }
                $training->push();
                if ($training->delete()) {
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
        $trainingDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $trainingDraft = $trainingDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $trainingDraft = $trainingDraft->where('status', self::STATUS_DRAFT);
        }
        $trainingDraft = $trainingDraft->get();
        if(count($trainingDraft) > 0) {
            $title = Lang::get('project::view.Add object for Training plan');
            $content .= view('project::template.content-task', ['inputs' => $trainingDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $trainingDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $trainingDraftEdit = $trainingDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $trainingDraftEdit = $trainingDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $trainingDraftEdit = $trainingDraftEdit->get();
        if(count($trainingDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Training plan');
            $content .= view('project::template.content-task', ['inputs' => $trainingDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }
        
        $trainingDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $trainingDelete = $trainingDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $trainingDelete = $trainingDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $trainingDelete = $trainingDelete->get();
        if(count($trainingDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Training plan');
            $content .= view('project::template.content-task', ['inputs' => $trainingDelete, 'title' => $title, 'type' => $typeWO])->render();
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
        $traningDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($traningDraft) > 0) {
            foreach($traningDraft as $traning) {
                $traning->status = self::STATUS_SUBMITTED;
                $traning->task_id = $task->id;
                $traning->save();
            }
        }

        $traningEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($traningEdit) > 0) {
            foreach($traningEdit as $traning) {
                $traning->status = self::STATUS_SUBMIITED_EDIT;
                $traning->task_id = $task->id;
                $traning->save();
            }
        }

        $traningDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($traningDelete)) {
            foreach($traningDelete as $traning) {
                $traning->status = self::STATUS_SUBMMITED_DELETE;
                $traning->task_id = $task->id;
                $traning->save();
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
        $traningDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $traningDraft = $traningDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $traningDraft = $traningDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $traningDraft = $traningDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $traningDraft = $traningDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $traningDraft = $traningDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $traningDraft = $traningDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $traningDraft = $traningDraft->get();
        if(count($traningDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($traningDraft as $traning) {
                        $traningParent = self::find($traning->parent_id);
                        $traning->delete();
                        if($traningParent) {
                            $traningParent->delete();
                        }
                    }
                } else {
                    foreach($traningDraft as $traning) {
                        $traningParent = self::find($traning->parent_id);
                        if($traningParent) {
                            $traning->parent_id = null;
                            $traning->task_id = null;
                            $traning->save();
                            $traningParent->delete();
                        }
                        $traning->status = self::STATUS_APPROVED;
                        $traning->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($traningDraft as $traning) {
                    if ($traning->status == self::STATUS_SUBMITTED) {
                        $traning->status = self::STATUS_REVIEWED;
                    }
                    if ($traning->status == self::STATUS_SUBMIITED_EDIT) {
                        $traning->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($traning->status == self::STATUS_SUBMMITED_DELETE) {
                        $traning->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $traning->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($traningDraft as $traning) {
                    if ($traning->status == self::STATUS_SUBMITTED ||
                        $traning->status == self::STATUS_REVIEWED) {
                        $traning->status = self::STATUS_FEEDBACK;
                    }
                    if ($traning->status == self::STATUS_SUBMIITED_EDIT ||
                        $traning->status == self::STATUS_REVIEWED_EDIT) {
                        $traning->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($traning->status == self::STATUS_SUBMMITED_DELETE ||
                        $traning->status == self::STATUS_REVIEWED_DELETE) {
                        $traning->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $traning->save();
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
        $allTrainings = self::getAllTraining($project->id);
        $allEmployee = Employee::getAllEmployee();
        $arrayCoo = CoreConfigData::getCOOAccount();
        return view('project::components.training-plan', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder, 'allTrainings' => $allTrainings, 'detail' => true, 'allEmployee' => $allEmployee, 'arrayCoo' => $arrayCoo])->render();
    }

    public static function getStatusResult()
    {
        return [
            self::STATUS_RESULT_PASS => Lang::get('project::view.Pass'),
            self::STATUS_RESULT_FAIL => Lang::get('project::view.Fail'),
        ];
    }

    /*
     * Get all member of training
     * @parram int
     * @return array
     */
    public static function getAllMemberOfTraining($id) 
    {
        $training = self::find($id);
        if (!$training) {
            return;
        }
        $members = array();
        foreach ($training->traningMember as $member) {
            array_push($members, $member->id);
        }
        return $members;
    }

    /**
     * check error time where submit workorder
     * @param int
     * @param array
     * @return boolean
     */
    public static function checkErrorTime($projectId, $projectDraft)
    {
        $tranings = self::where(function ($query) use ($projectDraft) {
                        $query->orWhereDate('start_at', '<', $projectDraft->start_at)
                          ->orWhereDate('start_at', '>', $projectDraft->end_at)
                          ->orWhereDate('end_at', '<', $projectDraft->start_at)
                          ->orWhereDate('end_at', '>', $projectDraft->end_at);;
                    })
                    ->where('project_id', $projectId)
                    ->get();
        $checkError = false;
        foreach($tranings as $traning) {
            if ($traning->projectTrainingChild) {
                continue;
            }
            $checkError = true;
            break;
        }
        return $checkError;
    }
    
    /**
     * update time for Traning plan of project
     * @param model $project
     * @param model $projectDraf
     */
    public static function updateTime($project, $projectDraf) {
        $projStart = $project->start_at;
        $projEnd = $project->end_at;
        if($projectDraf) {
            $projStart = $projectDraf->start_at;
            $projEnd = $projectDraf->end_at;
        }
        $plans = self::where('project_id', $project->id)
                ->whereNull('deleted_at')
                ->get();
        foreach ($plans as $plan) {
            $change = false;
            if ($plan->start_at > $projEnd) {
                $change = true;
                $plan->start_at = $projEnd;
            } elseif ($plan->start_at < $projStart) {
                $change = true;
                $plan->start_at = $projStart;
            }
            if ($plan->end_at > $projEnd) {
                $change = true;
                $plan->end_at = $projEnd;
            } elseif ($plan->end_at < $projStart) {
                $change = true;
                $plan->end_at = $projStart;
            }
            if ($change) {
                $plan->save();
            }
        }
        CacheHelper::forget(self::KEY_CACHE_WO, $project->id);
    }
}