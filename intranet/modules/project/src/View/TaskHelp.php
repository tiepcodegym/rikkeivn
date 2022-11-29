<?php

namespace Rikkei\Project\View;

use Rikkei\Project\Model\Task;
use Carbon\Carbon;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View as CoreView;

class TaskHelp
{
    public function fieldsChanged($oldTask, $oldAssignee, $newTask, $dataTaskAssigns, $dataTaskParticipants)
    {
        if (!$oldTask || !$newTask) {
            return null;
        }

        $results = [];
        $fields = ['title', 'created_at', 'type', 'content', 'solution', 'report_content'];
        foreach ($fields as $field) {
            if ($field === 'created_at') {
                $oldField = $oldTask->$field->toDateString();
            } else {
                $oldField = $oldTask->$field;
            }
            if ($this->isFieldChanged($oldField, $newTask[$field])) {
                if ($field === 'type') {
                    $results[] = [
                        'field' => $field,
                        'old' => empty($oldField) ? '' : Task::typeLabel()[$oldField],
                        'new' => empty($newTask[$field]) ? '' : Task::typeLabel()[$newTask[$field]],
                    ];
                } else {
                    $results[] = [
                        'field' => $field,
                        'old' => empty($oldField) ? '' : $oldField,
                        'new' => empty($newTask[$field]) ? '' : $newTask[$field],
                    ];
                }
            }
        }

        //Check changed assignee
        $results = array_merge($results, $this->getChangedAssignee($oldAssignee['role'], $dataTaskAssigns, TaskAssign::ROLE_OWNER));
        $results = array_merge($results, $this->getChangedAssignee($oldAssignee['role'], $dataTaskParticipants, TaskAssign::ROLE_PARTICIPANT));

        return $results;
    }

    /**
     * Get changed assignee
     *
     * @param array $oldAssignee
     * @param array $newAssignee
     * @param int $role
     *
     * @return array
     */
    private function getChangedAssignee($oldAssignee, $newAssignee, $role)
    {
        $dataBefore = $this->empIdOfAssignee($oldAssignee, $role);

        $results = [];
        if ($dataBefore != $newAssignee) {
            $textBefore = '';
            if (!empty($dataBefore)) {
                $empAssignee = Employee::getEmpByIds($dataBefore);
                foreach ($empAssignee as $emp) {
                    $textBefore .= empty($textBefore) ?
                            View::getNickName($emp->email) : ', ' . CoreView::getNickName($emp->email);
                }
            }

            $textAfter = '';
            if (!empty($newAssignee)) {
                $empAssignee = Employee::getEmpByIds($newAssignee);
                foreach ($empAssignee as $emp) {
                    $textAfter .= empty($textAfter) ?
                            View::getNickName($emp->email) : ', ' . CoreView::getNickName($emp->email);
                }
            }

            $results[] = [
                'field' => TaskAssign::roleLabel()[$role],
                'old' =>  $textBefore,
                'new' =>  $textAfter,
            ];
        }

        return $results;
    }

    private function empIdOfAssignee($data, $role)
    {
        if (empty($data) || !count($data)) {
            return [];
        }

        $ids = [];
        foreach ($data as $key => $value) {
            if ($role == $key) {
                if (!empty($value) && count($value)) {
                    foreach ($value as $item) {
                        $ids[] = $item['employee_id'];
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Check field has changed or not
     *
     * @param int|string|date $oldField
     * @param int|string|date $newField
     *
     * @return boolean
     */
    public function isFieldChanged($oldField, $newField)
    {
        return (empty($oldField) && !empty($newField))
            || (empty($newField) && !empty($oldField))
            || ($oldField != $newField);
    }

    /**
     * Check task is customer feedback (positive, negative)
     *
     * @param int $typeTask
     *
     * @return boolean
     */
    public function isCustomerFeedback($typeTask)
    {
        return in_array($typeTask, [Task::TYPE_COMMENDED, Task::TYPE_CRITICIZED]);
    }
}

