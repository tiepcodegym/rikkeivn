<?php

namespace Rikkei\Project\Model;

use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;
use Rikkei\Project\Model\ProjectWONote;
use Rikkei\Project\Model\ProjectMember;

class Performance extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_performances';

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
    protected $fillable = ['project_id', 'end_at'];

    /**
     * Get the performance child
     */
    public function projectPerformanceChild() {
        return $this->hasOne('Rikkei\Project\Model\projectPerformanceChild', 'parent_id');
    }                        

    /*
     * get  performance by project id
     * @param int
     * @return collection
     */
    public static function getPerformance($projectId)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE_WO, $projectId)) {
            return $item;
        }
        $item = self::select(['id', 'end_at', 'status', 'project_id'])
                      ->where('project_id', $projectId)->first();
        CacheHelper::put(self::KEY_CACHE_WO, $item, $projectId);
        return $item;
    }

    /*
     * add performance
     * @param array
     */
    public static function insertPerformance($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_PERFORMANCE];
            if (config('project.workorder_approved.performance')) {
                if(isset($input['isAddNew'])) {
                    $performance = new Performance();
                    $performance->status = self::STATUS_DRAFT;
                    $performance->project_id = $input['project_id'];
                    $performance->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                } else {
                    $performance = Performance::find($input['id']);
                    if ($performance->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_FEEDBACK_EDIT;
                        $performance->status = self::STATUS_DRAFT_EDIT;
                    }
                    if ($performance->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_FEEDBACK;
                        $performance->status = self::STATUS_DRAFT;
                    }
                    if ($performance->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DRAFT_EDIT;
                    }
                    if ($performance->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_UPDATED_DRAFT;
                    }
                }
                $performance->end_at = $input['end_at'];
                $performanceAttributes = $performance->attributes;
                $performanceOrigin = $performance->original;
                $performance->save();

                $statusText = $arrayLablelStatus[$status];
                if ($status == self::STATUS_DRAFT || $status == self::STATUS_EDIT_APPROVED) {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                } else {
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, $performanceAttributes, $performanceOrigin);
                }
            } else {
                $project = Project::getProjectById($input['project_id']);
                $project->end_at = $input['end_at'];
                $project->save();
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
     * delete performane
     * @param array
     * @return boolean
     */
    public static function deletePerformance($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_PERFORMANCE];
        $performance = self::find($input['id']);
        if ($performance) {
            if($performance->status == self::STATUS_APPROVED) {
                $performanceDelete = $performance->replicate();
                $performanceDelete->status = self::STATUS_DRAFT_DELETE;
                $performanceDelete->parent_id = $input['id'];
                $status = self::STATUS_DELETE_APPROVED;
                if ($performanceDelete->save()) {
                    CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                    $statusText = $arrayLablelStatus[$status];
                    View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                    return true;
                }
            } else {
                if ($performance->status == self::STATUS_DRAFT_EDIT) {
                    $status = self::STATUS_DELETE_DRAFT_EDIT;
                } else if ($performance->status == self::STATUS_DRAFT) {
                    $status = self::STATUS_DELETE_DRAFT;
                } else if ($performance->status == self::STATUS_FEEDBACK_DELETE) {
                    $status = self::STATUS_FEEDBACK_DELETE;
                } else if ($performance->status == self::STATUS_FEEDBACK_EDIT) {
                    $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                }  else if ($performance->status == self::STATUS_FEEDBACK) {
                    $status = self::STATUS_DELETE_FEEDBACK;
                }  else if ($performance->status == self::STATUS_DRAFT_DELETE) {
                    $status = self::STATUS_DRAFT_DELETE;
                }
                if ($performance->delete()) {
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
        $performanceDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $performanceDraft = $performanceDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $performanceDraft = $performanceDraft->where('status', self::STATUS_DRAFT);
        }
        $performanceDraft = $performanceDraft->get();
        if(count($performanceDraft) > 0) {
            $title = Lang::get('project::view.Change time end project for Performance');
            $content .= view('project::template.content-task', ['inputs' => $performanceDraft, 'title' => $title, 'type' => $typeWO])->render();
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
        $performanceDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($performanceDraft) > 0) {
            foreach($performanceDraft as $performance) {
                $performance->status = self::STATUS_SUBMITTED;
                $performance->task_id = $task->id;
                $performance->save();
            }
        }

        $performanceEdit = self::where('project_id', $input['project_id'])
                                        ->where('status', self::STATUS_DRAFT_EDIT)
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($performanceEdit) > 0) {
            foreach($performanceEdit as $performance) {
                $performance->status = self::STATUS_SUBMIITED_EDIT;
                $performance->task_id = $task->id;
                $performance->save();
            }
        }

        $performanceDelete = self::where('project_id', $input['project_id'])
                                    ->where('status', self::STATUS_DRAFT_DELETE)
                                    ->get();
        if(count($performanceDelete)) {
            foreach($deilverableDelete as $performance) {
                $performance->status = self::STATUS_SUBMMITED_DELETE;
                $performance->task_id = $task->id;
                $performance->save();
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
        $performanceDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $performanceDraft = $performanceDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $performanceDraft = $performanceDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $performanceDraft = $performanceDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $performanceDraft = $performanceDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $performanceDraft = $performanceDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $performanceDraft = $performanceDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $performanceDraft = $performanceDraft->get();
        $project = Project::find($projectId);
        if(count($performanceDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($performanceDraft as $performance) {
                        $performanceParent = self::find($performance->parent_id);
                        $performance->delete();
                        if($performanceParent) {
                            $performanceParent->delete();
                        }
                    }
                } else {
                    foreach($performanceDraft as $performance) {
                        $performanceParent = self::find($performance->parent_id);
                        if($performanceParent) {
                            $performanceParent->delete();
                        }
                        $endAt = $performance->end_at;
                        $performance->delete();
                    }
                    if ($endAt) {
                        $project = Project::find($projectId);
                        $project->end_at = $endAt;
                        $project->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($performanceDraft as $performance) {
                    if ($performance->status == self::STATUS_SUBMITTED) {
                        $performance->status = self::STATUS_REVIEWED;
                    }
                    if ($performance->status == self::STATUS_SUBMIITED_EDIT) {
                        $performance->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($performance->status == self::STATUS_SUBMMITED_DELETE) {
                        $performance->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $performance->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($performanceDraft as $performance) {
                    if ($performance->status == self::STATUS_SUBMITTED ||
                        $performance->status == self::STATUS_REVIEWED) {
                        $performance->status = self::STATUS_FEEDBACK;
                    }
                    if ($performance->status == self::STATUS_SUBMIITED_EDIT ||
                        $performance->status == self::STATUS_REVIEWED_EDIT) {
                        $performance->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($performance->status == self::STATUS_SUBMMITED_DELETE ||
                        $performance->status == self::STATUS_REVIEWED_DELETE) {
                        $performance->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $performance->save();
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
        $permissionUpdateNote = $permission['permissionEidt'];
        $projectNote = ProjectWONote::getProjectWoNote($project->id);
        $effort = View::generateValueElementInPerformance($project->id);
        $duration = View::getDurationProject($project);
        return view('project::components.performance', ['project' => $project, 'permissionUpdateNote' => $permissionUpdateNote,
            'projectNote' => $projectNote, 'effort' => $effort, 'duration' => $duration, 'detail' => true])->render();
    }
}