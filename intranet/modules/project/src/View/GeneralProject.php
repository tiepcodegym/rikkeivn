<?php

namespace Rikkei\Project\View;

use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Rikkei\Project\Model\TaskWoChange;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjDeliverable;
use Rikkei\Project\Model\StageAndMilestone;
use Illuminate\Support\Facades\Lang;
use DatePeriod;
use DateInterval;
use Rikkei\Project\Model\ProjectWOBase;

class GeneralProject
{
    /**
     * get nick name from email
     *
     * @param string $name
     * @return string
     */
    public static function getNickName($name)
    {
        return ucfirst(strtolower(preg_replace('/@.*/', '', $name)));
    }

    /**
     * get nick name from email not uptoer
     *
     * @param string $name
     * @return string
     */
    public static function getNickNameNormal($name)
    {
        return strtolower(preg_replace('/@.*/', '', $name));
    }

    /**
     * get Coo employee
     *
     * @return object
     */
    public static function getCOOEmployee()
    {
        $coo = CoreConfigData::getCOOAccount();
        return Employee::getEmpItemByEmail($coo, 2);
    }

    /**
     * get css class of status reviewer
     *
     * @param int $status
     * @param type $allStatus
     * @return string
     */
    public static function getClassCssStatusReviewer($status)
    {
        switch ($status) {
            case TaskAssign::STATUS_REVIEWED:
                return 'label label-info';
            case TaskAssign::STATUS_FEEDBACK:
                return 'label label-danger';
            case TaskAssign::STATUS_APPROVED:
                return 'label label-success';
            default:
                return null;
        }
    }

    /**
     * check access feedback
     *
     * @param type $feebacker
     * @return boolean
     */
    public static function isAccessFeedback($feebacker = null, $taskId = null)
    {
        if (!$feebacker || $feebacker->status != TaskAssign::STATUS_FEEDBACK) {
            $feebacker = TaskAssign::findAssigneeFeedback($taskId);
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        if ($feebacker->employee_id == $userCurrent->id) {
            return true;
        }
        if ($feebacker->role == TaskAssign::ROLE_REVIEWER) {
            if (Permission::getInstance()->isAllow('project-access::task.approve.review.save')) {
                return true;
            }
        } elseif ($feebacker->role == TaskAssign::ROLE_APPROVER) {
            if (Permission::getInstance()->isAllow('project-access::task.approve.save')) {
                return true;
            }
        }
        return false;
    }

    /**
     * check view baseline of last week for dashboard
     *      monday => active
     * @param Datetime $now
     * @return boolean
     */
    public static function isDBViewBLLW($now = null)
    {
        return false;
        if (!$now) {
            $now = Carbon::now();
        }
        if ($now->dayOfWeek == 1) {
            return true;
        }
        return false;
    }

    /**
     * check redirect baseline of last week for dashboard
     *      monday => active
     * @param Datetime $now
     * @return boolean
     */
    public static function isRedirectBLInDb($now = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        if ($now->dayOfWeek == 1) {
            return true;
        }
        return false;
    }

    /**
     * get flag url filter dashboard
     *
     * @return string
     */
    public static function getUrlFilterDb()
    {
        return trim(URL::route('project::dashboard'), '/') . '/';
    }

    /**
     * check date is last week of now
     *
     * @param type $date
     * @param type $now
     * @return boolean
     */
    public static function isLastWeek($date, $now = null)
    {
        if (!$now) {
            $now = Carbon::now();
        }
        if (!is_object($date)) {
            $date = Carbon::parse($date);
        }
        $nowClone = clone $now;
        $nowClone->modify('Monday last week');
        $date = clone $date->startOfWeek();
        if ($nowClone->getTimestamp() == $date->getTimestamp()) {
            return true;
        }
        return false;
    }

    public static function getContentWoChangesHtml($taskId, $project)
    {
        $collection = TaskWoChange::getWoChanges($taskId);
        if (!count($collection)) {
            return [
                'htmlBasicInfo' => null,
                'htmlChanges' => null,
                'htmlModal' => null
            ];
        }

        $columnsLabel = [
            TaskWoChange::FLAG_BASIC_INFO => Project::getColumnChanges(),
            TaskWoChange::FLAG_TEAM_ALLOCATION => ProjectMember::getColumnChanges(),
            TaskWoChange::FLAG_DELIVER => ProjDeliverable::getColumnChanges(),
            TaskWoChange::FLAG_STAGE => StageAndMilestone::getColumnChanges(),
        ];
        $columnsLabel[TaskWoChange::FLAG_BASIC_INFO]['type_mm'] = 'Type resource';
        $flagsLabel = TaskWoChange::getAllLabelFlag();

        // get htm of basic info table
        $htmlBasicInfo = '';
        $itemFirst = $collection->first();
        $contentArray = json_decode($itemFirst->content, true);
        $htmlChangeIcon = '&nbsp;&nbsp; <strong><i class="fa fa-arrow-right"></i></strong> &nbsp;&nbsp;';
        if (isset($contentArray[TaskWoChange::FLAG_BASIC_INFO][TaskWoChange::FLAG_STATUS_EDIT])
        ) {
            //bill effort
            $contentArray =
                $contentArray[TaskWoChange::FLAG_BASIC_INFO][TaskWoChange::FLAG_STATUS_EDIT];
            //check change type mm
            $typeMMOld = isset($contentArray['type_mm']) ? $contentArray['type_mm'][TaskWoChange::FLAG_STATUS_EDIT_OLD] : $project->type_mm;
            $typeMMNew = isset($contentArray['type_mm']) ? $contentArray['type_mm'][TaskWoChange::FLAG_STATUS_EDIT_NEW] : $project->type_mm;
            $isChangeTypeMM = isset($contentArray['type_mm'])
                && $contentArray['type_mm'][TaskWoChange::FLAG_STATUS_EDIT_OLD] != $contentArray['type_mm'][TaskWoChange::FLAG_STATUS_EDIT_NEW]
                && $contentArray['type_mm'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null;

            $htmlBasicInfo .= '<tr>';
            $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('billable_effort',
                    $columnsLabel[TaskWoChange::FLAG_BASIC_INFO])
                . '</td>';
            $htmlBasicInfo .= '<td class="numeric">' .
                $contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD] .
                ' ' . $project->getLabelTypeMM($typeMMOld) . '</td>';
            if (($contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                    $contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW] &&
                    $contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null) ||
                $isChangeTypeMM) {
                //get new value
                $billableEffortNew = $contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW];
                if ($isChangeTypeMM && !$billableEffortNew) {
                    $billableEffortNew = $contentArray['billable_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD];
                }
                $htmlBasicInfo .= '<td class="numeric highlight">' .
                    $billableEffortNew .
                    ' ' . $project->getLabelTypeMM($typeMMNew) . '</td>';
            } else {
                $htmlBasicInfo .= '<td></td>';
            }
            $htmlBasicInfo .= '</tr>';
            //approved production cost
            $htmlBasicInfo .= '<tr>';
            $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('cost_approved_production', $columnsLabel[TaskWoChange::FLAG_BASIC_INFO]) . '</td>';
            $oldApprovedCost = isset($contentArray['cost_approved_production']) ?
                $contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_OLD] : null;
            $htmlBasicInfo .= '<td class="numeric">'
                . ($oldApprovedCost . ' ' . $project->getLabelTypeMM($typeMMOld))
                . '</td>';
            if (isset($contentArray['cost_approved_production']) &&
                ($contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                    $contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_NEW] &&
                    $contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null) ||
                $isChangeTypeMM) {
                //get new value
                $costAprovedProd = isset($contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_NEW]) ?
                    $contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_NEW] : null;
                if ($isChangeTypeMM && !$costAprovedProd) {
                    $costAprovedProd = isset($contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_OLD]) ?
                        $contentArray['cost_approved_production'][TaskWoChange::FLAG_STATUS_EDIT_OLD] : null;
                }
                $htmlBasicInfo .= '<td class="numeric highlight">' . $costAprovedProd . ' ' . $project->getLabelTypeMM($typeMMNew) . '</td>';
            } else {
                $htmlBasicInfo .= '<td></td>';
            }
            //plan effort
            $htmlBasicInfo .= '<tr>';
            $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('plan_effort',
                    $columnsLabel[TaskWoChange::FLAG_BASIC_INFO])
                . '</td>';
            $htmlBasicInfo .= '<td class="numeric">' .
                $contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD] .
                ' ' . $project->getLabelTypeMM($typeMMOld) . '</td>';
            if (($contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                    $contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW] &&
                    $contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null) ||
                $isChangeTypeMM) {
                //get new value
                $planEffortNew = $contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_NEW];
                if ($isChangeTypeMM && !$planEffortNew) {
                    $planEffortNew = $contentArray['plan_effort'][TaskWoChange::FLAG_STATUS_EDIT_OLD];
                }
                $htmlBasicInfo .= '<td class="numeric highlight">' .
                    $planEffortNew .
                    ' ' . $project->getLabelTypeMM($typeMMNew) . '</td>';
            } else {
                $htmlBasicInfo .= '<td></td>';
            }
            $htmlBasicInfo .= '</tr>';

            //start at
            $htmlBasicInfo .= '<tr>';
            $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('start_at',
                    $columnsLabel[TaskWoChange::FLAG_BASIC_INFO])
                . '</td>';
            $htmlBasicInfo .= '<td class="numeric">' .
                $contentArray['start_at'][TaskWoChange::FLAG_STATUS_EDIT_OLD] .
                '</td>';
            if ($contentArray['start_at'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                $contentArray['start_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] &&
                $contentArray['start_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null) {
                $htmlBasicInfo .= '<td class="numeric highlight">' .
                    $contentArray['start_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] .
                    '</td>';
            } else {
                $htmlBasicInfo .= '<td></td>';
            }
            $htmlBasicInfo .= '</tr>';

            //end at
            $htmlBasicInfo .= '<tr>';
            $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('end_at',
                    $columnsLabel[TaskWoChange::FLAG_BASIC_INFO])
                . '</td>';
            $htmlBasicInfo .= '<td class="numeric">' .
                $contentArray['end_at'][TaskWoChange::FLAG_STATUS_EDIT_OLD] .
                '</td>';
            if ($contentArray['end_at'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                $contentArray['end_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] &&
                $contentArray['end_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] !== null) {
                $htmlBasicInfo .= '<td class="numeric highlight">' .
                    $contentArray['end_at'][TaskWoChange::FLAG_STATUS_EDIT_NEW] .
                    '</td>';
            } else {
                $htmlBasicInfo .= '<td></td>';
            }
            $htmlBasicInfo .= '</tr>';

            //close date
            if (isset($contentArray['close_date'])) {
                $htmlBasicInfo .= '<tr>';
                $htmlBasicInfo .= '<td>' . self::getLabelOrKeyArray('close_date',
                        $columnsLabel[TaskWoChange::FLAG_BASIC_INFO])
                    . '</td>';
                $htmlBasicInfo .= '<td class="numeric">' .
                    $contentArray['close_date'][TaskWoChange::FLAG_STATUS_EDIT_OLD] .
                    '</td>';
                if ($contentArray['close_date'][TaskWoChange::FLAG_STATUS_EDIT_OLD] !=
                    $contentArray['close_date'][TaskWoChange::FLAG_STATUS_EDIT_NEW]) {
                    $htmlBasicInfo .= '<td class="numeric highlight">' .
                        $contentArray['close_date'][TaskWoChange::FLAG_STATUS_EDIT_NEW] .
                        '</td>';
                } else {
                    $htmlBasicInfo .= '<td></td>';
                }
                $htmlBasicInfo .= '</tr>';
            }
        }
        // get html of changes list
        $htmlChanges = '<div class="panel-group" id="changeslog" role="tablist">';
        $htmlModal = '';
        $i = 1;
        foreach ($collection as $item) {
            $contentArray = json_decode($item->content, true);
            if (!count($contentArray)) {
                continue;
            }
            $htmlChanges .= '<div class="panel panel-primary">';
            $htmlChanges .= '<div class="panel-heading" role="tab">';
            $htmlChanges .= '<h4 class="panel-title">';
            $htmlChanges .= '<a data-toggle="collapse" data-parent="#changeslog" href="#change'
                . $i . '" aria-expanded="' . ($i == 1 ? 'true' : 'false') . '">' .
                $item->created_at . '</a>';
            $htmlChanges .= '</h4>';
            $htmlChanges .= '</div>';
            $htmlChanges .= '<div id="change' . $i . '" '
                . 'class="panel-collapse collapse' . ($i == 1 ? ' in' : '') . '" role="tabpanel" aria-expanded="'
                . ($i == 1 ? 'true' : 'false') . '">';
            $htmlChanges .= '<div class="panel-body">';
            $htmlChanges .= '<ul class="changes">';
            foreach ($contentArray as $typeObject => $contentObject) {
                $typeStatus = isset($contentObject[TaskWoChange::FLAG_TYPE_TEXT]) ?
                    $contentObject[TaskWoChange::FLAG_TYPE_TEXT] :
                    $contentObject[TaskWoChange::FLAG_TYPE_MULTI];
                if (!isset($columnsLabel[$typeObject]) || !$columnsLabel[$typeObject]) {
                    continue;
                }
                // show object add
                if (isset($contentObject[TaskWoChange::FLAG_STATUS_ADD]) &&
                    count($contentObject[TaskWoChange::FLAG_STATUS_ADD])
                ) {
                    $htmlChanges .= '<li>' . Lang::get('project::view.Add') .
                        ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel)
                        . ' &nbsp;' . self::getLinkViewDetail(
                            $typeObject . '-' . TaskWoChange::FLAG_STATUS_ADD . '-' . $item->id)
                        . '</li>';
                    $htmlModal .= '<div id="modal-wo-' . $typeObject . '-' .
                        TaskWoChange::FLAG_STATUS_ADD . '-' . $item->id . '" class="modal fade in">';
                    $htmlModal .= '<div class="modal-dialog modal-full-width">';
                    $htmlModal .= '<div class="modal-content">';
                    $htmlModal .= '<div class="modal-header">';
                    $htmlModal .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    $htmlModal .= '<span aria-hidden="true">×</span></button>';
                    $htmlModal .= '<h4 class="modal-title">';
                    $htmlModal .= Lang::get('project::view.Add') .
                        ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel);
                    $htmlModal .= '</h4>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '<div class="modal-body">';
                    $htmlModal .= '<div class="table-responsive">';
                    $htmlModal .= '<table class="table table-bordered">';
                    $htmlModal .= '<thead>';
                    $htmlModal .= '<tr>';
                    // show head table = column
                    foreach ($columnsLabel[$typeObject] as $labelColumn) {
                        $htmlModal .= '<th>' . $labelColumn . '</th>';
                    }
                    $htmlModal .= '</thead>';
                    $htmlModal .= '</tr>';
                    foreach ($contentObject[TaskWoChange::FLAG_STATUS_ADD]
                             as $contentElementObject
                    ) {
                        $htmlModal .= '<tr>';
                        foreach ($columnsLabel[$typeObject] as $column => $labelColumn) {
                            $htmlModal .= '<td>';
                            if (isset($contentElementObject[$column])) {
                                $htmlModal .= e($contentElementObject[$column]);
                            }
                            $htmlModal .= '</td>';
                        }
                        $htmlModal .= '</tr>';
                    }
                    $htmlModal .= '</table>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '<div class="modal-footer">';
                    $htmlModal .= '<button type="button" class="btn btn-default"'
                        . ' data-dismiss="modal">'
                        . trans('project::view.Close') . '</button>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                } // end add item

                // show object delete
                if (isset($contentObject[TaskWoChange::FLAG_STATUS_DELETE]) &&
                    count($contentObject[TaskWoChange::FLAG_STATUS_DELETE])
                ) {
                    $htmlChanges .= '<li>' . Lang::get('project::view.Delete') .
                        ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel)
                        . ' &nbsp;' . self::getLinkViewDetail(
                            $typeObject . '-' . TaskWoChange::FLAG_STATUS_DELETE . '-' . $item->id) . '</li>';
                    $htmlModal .= '<div id="modal-wo-' . $typeObject . '-' .
                        TaskWoChange::FLAG_STATUS_DELETE . '-' . $item->id . '" class="modal fade in">';
                    $htmlModal .= '<div class="modal-dialog modal-full-width">';
                    $htmlModal .= '<div class="modal-content">';
                    $htmlModal .= '<div class="modal-header">';
                    $htmlModal .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    $htmlModal .= '<span aria-hidden="true">×</span></button>';
                    $htmlModal .= '<h4 class="modal-title">';
                    $htmlModal .= Lang::get('project::view.Delete') .
                        ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel);
                    $htmlModal .= '</h4>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '<div class="modal-body">';
                    $htmlModal .= '<div class="table-responsive">';
                    $htmlModal .= '<table class="table table-bordered">';
                    $htmlModal .= '<thead>';
                    $htmlModal .= '<tr>';
                    // show head table = column
                    foreach ($columnsLabel[$typeObject] as $labelColumn) {
                        $htmlModal .= '<th>' . $labelColumn . '</th>';
                    }
                    $htmlModal .= '</thead>';
                    $htmlModal .= '</tr>';
                    foreach ($contentObject[TaskWoChange::FLAG_STATUS_DELETE]
                             as $contentElementObject
                    ) {
                        $htmlModal .= '<tr>';
                        foreach ($columnsLabel[$typeObject] as $column => $labelColumn) {
                            $htmlModal .= '<td>';
                            if (isset($contentElementObject[$column])) {
                                $htmlModal .= e($contentElementObject[$column]);
                            }
                            $htmlModal .= '</td>';
                        }
                        $htmlModal .= '</tr>';
                    }
                    $htmlModal .= '</table>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '<div class="modal-footer">';
                    $htmlModal .= '<button type="button" class="btn btn-default"'
                        . ' data-dismiss="modal">'
                        . trans('project::view.Close') . '</button>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                    $htmlModal .= '</div>';
                } // end delete item

                // show object edit
                $fieldWithout = ['is_important'];
                if (isset($contentObject[TaskWoChange::FLAG_STATUS_EDIT]) &&
                    count($contentObject[TaskWoChange::FLAG_STATUS_EDIT])
                ) {
                    if ($typeStatus == TaskWoChange::FLAG_TYPE_MULTI) {
                        $htmlChanges .= '<li>' . Lang::get('project::view.Modify') .
                            ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel)
                            . ' &nbsp;' . self::getLinkViewDetail(
                                $typeObject . '-' . TaskWoChange::FLAG_STATUS_EDIT . '-' . $item->id) . '</li>';
                        $htmlModal .= '<div id="modal-wo-' . $typeObject . '-' .
                            TaskWoChange::FLAG_STATUS_EDIT . '-' . $item->id . '" class="modal fade in">';
                        $htmlModal .= '<div class="modal-dialog modal-full-width">';
                        $htmlModal .= '<div class="modal-content">';
                        $htmlModal .= '<div class="modal-header">';
                        $htmlModal .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                        $htmlModal .= '<span aria-hidden="true">×</span></button>';
                        $htmlModal .= '<h4 class="modal-title">';
                        $htmlModal .= Lang::get('project::view.Modify') .
                            ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel);
                        $htmlModal .= '</h4>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '<div class="modal-body">';
                        $htmlModal .= '<div class="table-responsive">';
                        $htmlModal .= '<table class="table table-bordered">';
                        $htmlModal .= '<thead>';
                        $htmlModal .= '<tr>';
                        // show head table = column
                        foreach ($columnsLabel[$typeObject] as $labelColumn) {
                            $htmlModal .= '<th>' . $labelColumn . '</th>';
                        }
                        $htmlModal .= '</thead>';
                        $htmlModal .= '</tr>';
                        foreach ($contentObject[TaskWoChange::FLAG_STATUS_EDIT]
                                 as $contentElementObject
                        ) {
                            // show old data
                            $htmlModal .= '<tr>';
                            foreach ($columnsLabel[$typeObject] as $column => $labelColumn) {
                                $htmlModal .= '<td>';
                                if (isset($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD][$column])) {
                                    $htmlModal .= e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD][$column]);
                                }
                                $htmlModal .= '</td>';
                            }
                            $htmlModal .= '</tr>';
                            // show new data
                            $htmlModal .= '<tr class="background-submitted-edit">';
                            foreach ($columnsLabel[$typeObject] as $column => $labelColumn) {
                                $htmlModal .= '<td>';
                                if (isset($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW][$column])) {
                                    $htmlModal .= e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW][$column]);
                                }
                                $htmlModal .= '</td>';
                            }
                            $htmlModal .= '</tr>';
                        }
                        $htmlModal .= '</table>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '<div class="modal-footer">';
                        $htmlModal .= '<button type="button" class="btn btn-default"'
                            . ' data-dismiss="modal">'
                            . trans('project::view.Close') . '</button>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '</div>';
                        $htmlModal .= '</div>';
                        // end show multi type
                    } else {
                        foreach ($contentObject[TaskWoChange::FLAG_STATUS_EDIT]
                                 as $fieldChange => $contentElementObject
                        ) {
                            if ($fieldChange != 'close_date' || ($fieldChange == 'close_date' &&
                                    $contentObject[TaskWoChange::FLAG_STATUS_EDIT]['close_date'][TaskWoChange::FLAG_STATUS_EDIT_NEW] === $contentObject[TaskWoChange::FLAG_STATUS_EDIT]['close_date'][TaskWoChange::FLAG_STATUS_EDIT_OLD])) {
                                if (!array_key_exists(TaskWoChange::FLAG_STATUS_EDIT_OLD, $contentElementObject) ||
                                    !array_key_exists(TaskWoChange::FLAG_STATUS_EDIT_NEW, $contentElementObject) ||
                                    ((!$contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD] ||
                                        !$contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW])) && !in_array($fieldChange, $fieldWithout)) {
                                    continue;
                                }
                                // show singe type
                                if (!isset($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD]) ||
                                    !isset($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW]) ||
                                    ($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW] ==
                                        $contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD])) {
                                    continue;
                                }
                            }

                            // get label is_important
                            if ($fieldChange == 'is_important') {
                                ($contentElementObject['new'] == 1) ? $contentElementObject['new'] = 'Is important' : $contentElementObject['new'] = 'Not important';
                                ($contentElementObject['old'] == 1) ? $contentElementObject['old'] = 'Is important' : $contentElementObject['old'] = 'Not important';
                            }

                            $htmlChanges .= '<li>' . Lang::get('project::view.Modify') .
                                ' ' . TaskWoChange::getLabelFlag($typeObject, $flagsLabel)
                                . ': ';
                            // show old data
                            $htmlChanges .= self::getLabelOrKeyArray($fieldChange,
                                    $columnsLabel[$typeObject]) . ': ' .
                                (($fieldChange == 'type_mm') ? $project->getLabelTypeMM(e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD])) : e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_OLD]))
                                . '&nbsp;';
                            $htmlChanges .= $htmlChangeIcon;
                            // show new data
                            $htmlChanges .=
                                (($fieldChange == 'type_mm') ? $project->getLabelTypeMM(e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW])) : e($contentElementObject[TaskWoChange::FLAG_STATUS_EDIT_NEW]))
                                . '&nbsp;';
                            // end show singe type
                            $htmlChanges .= '</li>';
                        }
                    }
                }
            }
            $htmlChanges .= '</ul>';
            $htmlChanges .= '</div>';
            $htmlChanges .= '</div>';
            $htmlChanges .= '</div>';
            $i++;
        }
        $htmlChanges .= '</div>';
        return [
            'htmlBasicInfo' => $htmlBasicInfo,
            'htmlChanges' => $htmlChanges,
            'htmlModal' => $htmlModal
        ];
    }

    /**
     * get value or key or array follow key
     *
     * @param type $key
     * @param array $array
     * @return type
     */
    public static function getLabelOrKeyArray($key, array $array)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $key;
    }

    /**
     * get link view detail
     *
     * @param string $idDom
     * @return string
     */
    public static function getLinkViewDetail($idDom)
    {
        return '<a href="#" data-toggle="modal" data-target="#modal-wo-' . $idDom .
            '"><i>' . Lang::get('project::view.View detail') . '</i></a>';
    }

    /**
     * get man day follow each month
     *
     * @param Datetime $start
     * @param Datetime $end
     * @param float $effort
     */
    public static function getManDayAndEffortEachMonth($start, $end, $effort)
    {
        $result = [];
        $lastDayMonthOfEnddate = clone $end;
        $lastDayMonthOfEnddate->endOfMonth()->startOfDay();
        $lastDayMonthOfEnddate->modify('+1 day');
        $startDayMonthOfEnddate = clone $start;
        $startDayMonthOfEnddate->startOfMonth();
        // modify 1 day because check < end => end not include
        $end->modify('+1 day');
        // period month from start to end
        $periodMonth = new DatePeriod($startDayMonthOfEnddate,
            new DateInterval('P1M'), $lastDayMonthOfEnddate);
        $startMonth = $start;
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $weekend = (array)CoreConfigData::get('project.weekend');
        foreach ($periodMonth as $lastDayMonth) {
            $lastDayMonth->endOfMonth()->startOfDay();
            $startOfDayMonth = clone $lastDayMonth;
            $startOfDayMonth->startOfMonth();
            $lastDayMonth->modify('+1 day');
            $periodDay = new DatePeriod($startOfDayMonth, new DateInterval('P1D'), $lastDayMonth);
            if ($end->diff($lastDayMonth)->invert) { // enddate > last date of month
                $daysWorks = $lastDayMonth->diff($startMonth)->days;
            } else {
                $daysWorks = $end->diff($startMonth)->days;
            }
            foreach ($periodDay as $dt) {
                $curr = $dt->format('D'); // day of week: mon,...
                $diffDtWidthStart = $dt->diff($start);
                $diffDtWidthEnd = $dt->diff($end);
                if (in_array($curr, $weekend) ||
                    in_array($dt->format('Y-m-d'), $specialHolidays) ||
                    in_array($dt->format('m-d'), $annualHolidays)
                ) {
                    // period in from start to end
                    if (($diffDtWidthStart->invert || $diffDtWidthStart->days == 0) &&
                        ($diffDtWidthEnd->invert == 0 && $diffDtWidthEnd->days != 0)
                    ) {
                        $daysWorks--;
                    }
                }
            }
            if ($daysWorks < 0) {
                $daysWorks = 0;
            }
            $result[$startOfDayMonth->format('Y-m')] = round($daysWorks *
                $effort / 100, 2);
            $startMonth = clone $lastDayMonth;
        }
        return $result;
    }

    /**
     * get period month from start to end
     *
     * @param Datetime $start
     * @param Datetime $end
     * @return array
     */
    public static function getPeriodMonthTime($start, $end)
    {
        $result = [];
        $lastDayMonthOfEnddate = clone $end;
        $lastDayMonthOfEnddate->endOfMonth()->startOfDay();
        $lastDayMonthOfEnddate->modify('+1 day');
        $startDayMonthOfEnddate = clone $start;
        $startDayMonthOfEnddate->startOfMonth();
        // modify 1 day because check < end => end not include
        $end->modify('+1 day');
        // period month from start to end
        $periodMonth = new DatePeriod($startDayMonthOfEnddate,
            new DateInterval('P1M'), $lastDayMonthOfEnddate);
        foreach ($periodMonth as $lastDayMonth) {
            $result[] = $lastDayMonth->format('Y-m');
        }
        return $result;
    }

    /**
     * get info project reward infomation
     *
     * @param object $projectRewardMeta
     * @return array
     */
    public static function projectRewardInfo($projectRewardMeta)
    {
        $result = [
            'reward_actual' => 0,
            'reward_qa' => 0,
            'reward_pqa' => 0,
            'reward_pm_dev' => 0,
            'reward_add' => 0,
        ];
        // actual = budget - number leakage * unit reward leakage
        $result['reward_actual'] = $projectRewardMeta->reward_budget
            - $projectRewardMeta->count_leakage
            * $projectRewardMeta->unit_reward_leakage_actual;
        if ($result['reward_actual'] < 0) {
            $result['reward_actual'] = 0;
        }
        // reward qa = number defect * unit defect - number leakage * unit leakage qa
        $result['reward_qa'] = $projectRewardMeta->count_defect
            * $projectRewardMeta->unit_reward_defect
            - $projectRewardMeta->count_leakage
            * $projectRewardMeta->unit_reward_leakage_qa;
        if ($result['reward_qa'] < 0) {
            $result['reward_qa'] = 0;
        } else if ($result['reward_qa'] > $result['reward_actual']) {
            $result['reward_qa'] = $result['reward_actual'];
        }
        // reward pqa = number defect pqa * unit defect pqa
        $result['reward_pqa'] = $projectRewardMeta->count_defect_pqa
            * $projectRewardMeta->unit_reward_defect_pqa;
        if ($result['reward_pqa'] < 0) {
            $result['reward_pqa'] = 0;
        } else if ($result['reward_pqa'] > $result['reward_actual']) {
            $result['reward_pqa'] = $result['reward_actual'];
        }
        // reward pm, dev, brse = reward actual - reward qa - reward pqa
        $result['reward_pm_dev'] = $result['reward_actual'] -
            $result['reward_qa'] - $result['reward_pqa'];
        if ($result['reward_pm_dev'] < 0) {
            $result['reward_pm_dev'] = 0;
        }
        return $result;
    }

    /**
     * check status delete
     *
     * @param int $status
     * @return boolean
     */
    public static function isStatusDelete($status)
    {
        $status = (int)$status;
        return in_array($status, [
            ProjectWOBase::STATUS_DRAFT_DELETE,
            ProjectWOBase::STATUS_SUBMMITED_DELETE,
            ProjectWOBase::STATUS_REVIEWED_DELETE,
            ProjectWOBase::STATUS_FEEDBACK_DELETE
        ]);
    }

    /**
     * check day before day another
     *
     * @param model $project
     * @param Carbon $dayAfter
     * @return boolean
     */
    public static function isProjectOverEnddate($project, $dayAfter)
    {
        if ($project->state != Project::STATE_PROCESSING) {
            return false;
        }
        $dayBefore = Carbon::parse($project->end_at);
        $diffBeforeWithAfter = $dayBefore->diff($dayAfter);
        // project over end date but not close
        if ($diffBeforeWithAfter->invert == 0 &&
            $diffBeforeWithAfter->days >= 1
        ) {
            return true;
        }
        return false;
    }
}
