<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Rikkei\Core\View\CookieCore;
use Rikkei\Project\Model\SoftwareCots;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Project\Model\Task;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;
use Rikkei\Project\Helper\AuthService;

class ToolAndInfrastructure extends ProjectWOBase
{
    use SoftDeletes;
    const SOFT_WARE = 'soft_ware';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proj_op_tools';

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
    protected $fillable = ['project_id', 'soft_hard_ware', 'purpose',
                            'note',
                            'status', 'type', 'state',
                            'start_at', 'type', 'start_date', 'end_date', 'software_id'];

    /**
     * Get the tool and infrastructure child
     */
    public function projectToolAndInfrastructureChild() {
        return $this->hasOne('Rikkei\Project\Model\ToolAndInfrastructure', 'parent_id');
    }

    /*
     * get all tool and infrastructures by project id
     * @param int
     * @return collection
     */
    public static function getAllToolAndInfrastructures($projectId)
    {
        $item = self::select(['id', 'soft_hard_ware', 'purpose', 'note', 'status', 'start_date', 'end_date','software_id'])
            ->where('project_id', $projectId);
        if (config('project.workorder_approved.tool_and_infrastructure')) {
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
     * add tool and infrastructure
     * @param array
     */
    public static function insertToolAndInfrastructure($input)
    {
        DB::beginTransaction();
        try {
            $arrayLablelStatus = self::getLabelStatusForProjectLog();
            $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE];
            if (config('project.workorder_approved.tool_and_infrastructure')) {
                if (isset($input['isAddNew'])) {
                    $tool = new ToolAndInfrastructure();
                    $tool->project_id = $input['project_id'];
                    $tool->status = self::STATUS_DRAFT;
                    $tool->soft_hard_ware = $input['soft_hard_ware_1'];
                    $tool->purpose = $input['purpose_1'];
                    $tool->note = $input['note_1'];
                    $tool->start_date = $input['start_date_1'];
                    $tool->end_date = $input['end_date_1'];
                    $tool->software_id = $input['soft_ware_id_1'];
                    $tool->created_by = Permission::getInstance()->getEmployee()->id;
                    $status = self::STATUS_DRAFT;
                    $tool->save();
                } else if (isset($input['isEdit'])) {
                    if (isset($input['status']) && $input['status'] == self::STATUS_APPROVED) {
                        $status = self::STATUS_EDIT_APPROVED;
                        $tool = new ToolAndInfrastructure();
                        $tool->project_id = $input['project_id'];
                        $tool->status = self::STATUS_DRAFT_EDIT;
                        $tool->parent_id = $input['id'];
                        $tool->created_by = Permission::getInstance()->getEmployee()->id;
                    } else {
                        $tool = self::find($input['id']);
                        if ($tool->status == self::STATUS_FEEDBACK_EDIT) {
                            $status = self::STATUS_FEEDBACK_EDIT;
                            $tool->status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($tool->status == self::STATUS_FEEDBACK) {
                            $status = self::STATUS_FEEDBACK;
                            $tool->status = self::STATUS_DRAFT;
                        }
                        if ($tool->status == self::STATUS_DRAFT_EDIT) {
                            $status = self::STATUS_DRAFT_EDIT;
                        }
                        if ($tool->status == self::STATUS_DRAFT) {
                            $status = self::STATUS_UPDATED_DRAFT;
                        }
                    }
                    $tool->soft_hard_ware = $input['soft_hard_ware_1'];
                    $tool->purpose = $input['purpose_1'];
                    $tool->start_date = $input['start_date_1'];
                    $tool->end_date = $input['end_date_1'];
                    $tool->software_id = $input['soft_ware_id_1'];
                    $tool->note = $input['note_1'];
                    $toolAttributes = $tool->attributes;
                    $toolOrigin = $tool->original;
                    $tool->save();
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
                    $tool = self::find($input['id']);
                } else {
                    $status = self::STATUS_ADD;
                    $tool = new ToolAndInfrastructure;
                    $tool->project_id = $input['project_id'];
                    $tool->created_by = Permission::getInstance()->getEmployee()->id;
                    $tool->status = self::STATUS_APPROVED;
                }
                $tool->soft_hard_ware = $input['soft_hard_ware_1'];
                $tool->purpose = $input['purpose_1'];
                $tool->start_date = $input['start_date_1'];
                $tool->end_date = $input['end_date_1'];
                $tool->software_id = $input['soft_ware_id_1'];
                $tool->note = $input['note_1'];
                $toolAttributes = $tool->attributes;
                $toolOrigin = $tool->original;
                $tool->save();


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
     * delete tool and infrastructure
     * @param array
     * @return boolean
     */
    public static function deleteToolAndInfrastructure($input)
    {
        $arrayLablelStatus = self::getLabelStatusForProjectLog();
        $labelElement = self::getLabelElementForWorkorder()[Task::TYPE_WO_TOOL_AND_INFRASTRUCTURE];
        if (config('project.workorder_approved.tool_and_infrastructure')) {
            $tool = self::find($input['id']);
            if ($tool) {
                if($tool->status == self::STATUS_APPROVED) {
                    $toolDelete = $tool->replicate();
                    $toolDelete->status = self::STATUS_DRAFT_DELETE;
                    $toolDelete->parent_id = $input['id'];
                    $status = self::STATUS_DELETE_APPROVED;
                    if ($toolDelete->save()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                } else {
                    if ($tool->status == self::STATUS_DRAFT_EDIT) {
                        $status = self::STATUS_DELETE_DRAFT_EDIT;
                    } else if ($tool->status == self::STATUS_DRAFT) {
                        $status = self::STATUS_DELETE_DRAFT;
                    } else if ($tool->status == self::STATUS_FEEDBACK_DELETE) {
                        $status = self::STATUS_FEEDBACK_DELETE;
                    } else if ($tool->status == self::STATUS_FEEDBACK_EDIT) {
                        $status = self::STATUS_DELETE_FEEDBACK_EDIT;
                    }  else if ($tool->status == self::STATUS_FEEDBACK) {
                        $status = self::STATUS_DELETE_FEEDBACK;
                    }  else if ($tool->status == self::STATUS_DRAFT_DELETE) {
                        $status = self::STATUS_DRAFT_DELETE;
                    }
                    if ($tool->delete()) {
                        CacheHelper::forget(self::KEY_CACHE_WO, $input['project_id']);
                        $statusText = $arrayLablelStatus[$status];
                        View::insertProjectLogWO($input['project_id'], $statusText, $labelElement, null, null);
                        return true;
                    }
                }
            }
        } else {
            $status = self::STATUS_DELETE;
            $tool = self::find($input['id']);
            if ($tool) {
                if ($tool->delete()) {
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
        $toolDraft = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $toolDraft = $toolDraft->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK]);
        } else {
            $toolDraft = $toolDraft->where('status', self::STATUS_DRAFT);
        }
        $toolDraft = $toolDraft->get();
        if(count($toolDraft) > 0) {
            $title = Lang::get('project::view.Add object for Tools and infrastructure');
            $content .= view('project::template.content-task', ['inputs' => $toolDraft, 'title' => $title, 'type' => $typeWO])->render();
        }

        $toolDraftEdit = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $toolDraftEdit = $toolDraftEdit->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT]);
        } else {
            $toolDraftEdit = $toolDraftEdit->where('status', self::STATUS_DRAFT_EDIT);
        }
        $toolDraftEdit = $toolDraftEdit->get();
        if(count($toolDraftEdit) > 0) {
            $title = Lang::get('project::view.Edit object for Tools and infrastructure');
            $content .= view('project::template.content-task', ['inputs' => $toolDraftEdit, 'title' => $title, 'type' => $typeWO])->render();
        }

        $toolDelete = self::where('project_id', $input['project_id']);
        if ($typeSubmit) {
            $toolDelete = $toolDelete->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE]);
        } else {
            $toolDelete = $toolDelete->where('status', self::STATUS_DRAFT_DELETE);
        }
        $toolDelete = $toolDelete->get();
        if(count($toolDelete) > 0) {
            $title = Lang::get('project::view.Delete object for Tools and infrastructure');
            $content .= view('project::template.content-task', ['inputs' => $toolDelete, 'title' => $title, 'type' => $typeWO])->render();
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
        $toolDraft = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_FEEDBACK])
                                    ->get();
        if(count($toolDraft) > 0) {
            foreach($toolDraft as $tool) {
                $tool->status = self::STATUS_SUBMITTED;
                $tool->task_id = $task->id;
                $tool->save();
            }
        }

        $toolEdit = self::where('project_id', $input['project_id'])
                                        ->whereIn('status', [self::STATUS_DRAFT_EDIT, self::STATUS_FEEDBACK_EDIT])
                                        ->whereNotNull('parent_id')
                                        ->get();
        if(count($toolEdit) > 0) {
            foreach($toolEdit as $tool) {
                $tool->status = self::STATUS_SUBMIITED_EDIT;
                $tool->task_id = $task->id;
                $tool->save();
            }
        }

        $toolDelete = self::where('project_id', $input['project_id'])
                                    ->whereIn('status', [self::STATUS_DRAFT_DELETE, self::STATUS_FEEDBACK_DELETE])
                                    ->get();
        if(count($toolDelete)) {
            foreach($toolDelete as $tool) {
                $tool->status = self::STATUS_SUBMMITED_DELETE;
                $tool->task_id = $task->id;
                $tool->save();
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
        $toolDraft = self::where('project_id', $projectId);
        if ($statusTask == Task::STATUS_APPROVED) {
            if ($type == self::TYPE_ADD) {
                $toolDraft = $toolDraft->where('status', self::STATUS_REVIEWED);
            } else if($type == self::TYPE_EDIT) {
                $toolDraft = $toolDraft->where('status', self::STATUS_REVIEWED_EDIT);
            } else {
                $toolDraft = $toolDraft->where('status', self::STATUS_REVIEWED_DELETE);
            }
        } else if ($statusTask == Task::STATUS_FEEDBACK || $statusTask == Task::STATUS_REVIEWED) {
            if ($type == self::TYPE_ADD) {
                $toolDraft = $toolDraft->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_REVIEWED]);
            } else if($type == self::TYPE_EDIT) {
                $toolDraft = $toolDraft->whereIn('status', [self::STATUS_SUBMIITED_EDIT, self::STATUS_REVIEWED_EDIT]);
            } else {
                $toolDraft = $toolDraft->whereIn('status', [self::STATUS_SUBMMITED_DELETE, self::STATUS_REVIEWED_DELETE]);
            }
        }
        $toolDraft = $toolDraft->get();
        if(count($toolDraft) > 0) {
            if ($statusTask == Task::STATUS_APPROVED) {
                if ($type == self::TYPE_DELETE) {
                    foreach($toolDraft as $tool) {
                        $toolParent = self::find($tool->parent_id);
                        $tool->delete();
                        if($toolParent) {
                            $toolParent->delete();
                        }
                    }
                } else {
                    foreach($toolDraft as $tool) {
                        $toolParent = self::find($tool->parent_id);
                        if($toolParent) {
                            $tool->parent_id = null;
                            $tool->task_id = null;
                            $tool->save();
                            $toolParent->delete();
                        }
                        $tool->status = self::STATUS_APPROVED;
                        $tool->save();
                    }
                }
            } else if ($statusTask == Task::STATUS_REVIEWED) {
                foreach($toolDraft as $tool) {
                    if ($tool->status == self::STATUS_SUBMITTED) {
                        $tool->status = self::STATUS_REVIEWED;
                    }
                    if ($tool->status == self::STATUS_SUBMIITED_EDIT) {
                        $tool->status = self::STATUS_REVIEWED_EDIT;
                    }
                    if ($tool->status == self::STATUS_SUBMMITED_DELETE) {
                        $tool->status = self::STATUS_REVIEWED_DELETE;
                    }
                    $tool->save();
                }
            } else if ($statusTask == Task::STATUS_FEEDBACK) {
                foreach($toolDraft as $tool) {
                    if ($tool->status == self::STATUS_SUBMITTED ||
                        $tool->status == self::STATUS_REVIEWED) {
                        $tool->status = self::STATUS_FEEDBACK;
                    }
                    if ($tool->status == self::STATUS_SUBMIITED_EDIT ||
                        $tool->status == self::STATUS_REVIEWED_EDIT) {
                        $tool->status = self::STATUS_FEEDBACK_EDIT;
                    }
                    if ($tool->status == self::STATUS_SUBMMITED_DELETE ||
                        $tool->status == self::STATUS_REVIEWED_DELETE) {
                        $tool->status = self::STATUS_FEEDBACK_DELETE;
                    }
                    $tool->save();
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
        $allToolAndInfrastructure = self::getAllToolAndInfrastructures($project->id);
        $authService = new AuthService();
        $login = $authService->loginGetToken();
        if ($login['statusCode'] != 200) {
            $response['error'] = $login['statusCode'];
            $response['message'] =  $login['message'];
            $response['content'] = view('project::components.tools-infrastructure', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder,
                'allToolAndInfrastructure' => $allToolAndInfrastructure,
                'detail' => true,
                'allSoftware' => []
            ])->render();

            return $response;
        }
        $listSoftware = $authService->getSoftware();
        if ($listSoftware['statusCode'] != 200) {
            $response['error'] = $login['statusCode'];
            $response['message'] =  $login['message'];
            $response['content'] = view('project::components.tools-infrastructure', ['permissionEdit' => $permissionEdit, 'checkEditWorkOrder' => $checkEditWorkOrder,
                'allToolAndInfrastructure' => $allToolAndInfrastructure,
                'detail' => true,
                'allSoftware' => []
            ])->render();

            return $response;
        }

        $allSoftware = self::transformSoftware($listSoftware['result']);

        return view('project::components.tools-infrastructure', [
            'permissionEdit' => $permissionEdit,
            'checkEditWorkOrder' => $checkEditWorkOrder,
            'allToolAndInfrastructure' => $allToolAndInfrastructure,
            'detail' => true,
            'allSoftware' => $allSoftware
        ])->render();
    }

    /**
     * check expiry date software
     * @param array
     * @return boolean
     */
    public static function isCheckEpiryDate($data)
    {
        if (isset($data['approved'])) {
            return [
                'error' => true,
                'content' => null
            ];
        }
        // Find item software in DB mongoDB
        $authService = new AuthService();
        // login in DB mongoDB
        $login = $authService->loginGetToken();
        if ($login['statusCode'] != 200) {
            $response['error'] = $login['statusCode'];
            $response['message'] =  $login['message'];

            return $response;
        }
        // get list item software mongoDB
        $arrSoftwares = $authService->getSoftware();
        $item = '';
        foreach ($arrSoftwares['result'] as $value) {
            if ($value->id == $data['soft_ware_id_1']) {
                $item = $value;
            }
        }
        // Process conditional check if start date and end date, less than or greater then error
        if(!($data['start_date_1'] >= Carbon::parse($item->startDate)->format('Y-m-d') && $data['end_date_1'] <= Carbon::parse($item->endDate)->format('Y-m-d')))
        {
            $starDate = max($data['start_date_1'], Carbon::parse($item->startDate)->format('Y-m-d'));
            $endaDate = min($data['end_date_1'], Carbon::parse($item->endDate)->format('Y-m-d'));

            if(($data['start_date_1'] < Carbon::parse($item->startDate)->format('Y-m-d') && $data['end_date_1'] < Carbon::parse($item->startDate)->format('Y-m-d'))
                || ($data['start_date_1'] > Carbon::parse($item->endDate)->format('Y-m-d') && $data['end_date_1'] > Carbon::parse($item->endDate)->format('Y-m-d'))) {
                $starDate = Carbon::parse($item->startDate)->format('Y-m-d');
                $endaDate = Carbon::parse($item->endDate)->format('Y-m-d');
            }

            return [
                'error' => false,
                'content' => trans('project::view.:date of software has passed the use date! Would you like to change it to :changedate',
                    [
                        'date' => $data['start_date_1'].' -> '. $data['end_date_1'],
                        'changedate' => $starDate. ' -> ' . $endaDate
                    ])
            ];
        }

        return [
            'error' => true,
            'content' => null
        ];
    }

    /**
     * transform data request
     * @param array
     * @return array
     */
    public static function tranformData($data)
    {
        if (isset($data['approved'])) {
            $authService = new AuthService();
            $login = $authService->loginGetToken();
            if ($login['statusCode'] != 200) {
                $response['error'] = $login['statusCode'];
                $response['message'] =  $login['message'];

                return $response;
            }
            $arrSoftwares = $authService->getSoftware();
            $item = '';
            foreach ($arrSoftwares['result'] as $value) {
                if ($value->id == $data['soft_ware_id_1']) {
                    $item = $value;
                }
            }
            // Process conditional check if start date and end date, less than or greater then error
            if(!($data['start_date_1'] >= Carbon::parse($item->startDate)->format('Y-m-d') && $data['end_date_1'] <= Carbon::parse($item->endDate)->format('Y-m-d')))
            {
                $starDate = max($data['start_date_1'], Carbon::parse($item->startDate)->format('Y-m-d'));
                $endaDate = min($data['end_date_1'], Carbon::parse($item->endDate)->format('Y-m-d'));

                if(($data['start_date_1'] < Carbon::parse($item->startDate)->format('Y-m-d') && $data['end_date_1'] < Carbon::parse($item->startDate)->format('Y-m-d'))
                    || ($data['start_date_1'] > Carbon::parse($item->endDate)->format('Y-m-d') && $data['end_date_1'] > Carbon::parse($item->endDate)->format('Y-m-d'))) {
                    $starDate = Carbon::parse($item->startDate)->format('Y-m-d');
                    $endaDate = Carbon::parse($item->endDate)->format('Y-m-d');
                }
            }

            return [
                'number_record'=> 1,
                'soft_hard_ware_1'=> $data['soft_hard_ware_1'],
                'soft_ware_id_1'=> $data['soft_ware_id_1'],
                'purpose_1'=> $data['purpose_1'],
                'note_1'=> $data['note_1'],
                'start_date_1'=> $starDate,
                'end_date_1'=> $endaDate,
                'project_id'=> $data['project_id']
            ];
        }

        return $data;
    }

    /**
     * transform data soft ware
     * @param array
     * @return array
     */
    public static function transformSoftware($data)
    {
        $arrNew = [];
        foreach ($data as $value) {
            $arrNew[$value->id] = $value->nameSoftware;
        }

        return $arrNew;
    }
}
