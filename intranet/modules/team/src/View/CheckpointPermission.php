<?php

namespace Rikkei\Team\View;

use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\View\CssPermission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\CheckpointResult;
use Rikkei\Team\Model\CheckpointResultDetail;
use Lang;
use Carbon\Carbon;

class CheckpointPermission
{
    /**
     *  store this object
     * @var object
     */
    protected static $instance;

    /**
     * Check employee has permission view checkpoint result page
     * @param Employee $emp
     * @param int $checkpointId
     * @return boolean
     */
    public function isAllow($emp, $checkpointId)
    {
        if (Permission::getInstance()->isAllow('team::checkpoint.made')) {
            return true;
        }

        if ($this->isLeader($emp, $checkpointId) 
                || $this->isRelate($emp, $checkpointId)
                || $this->isEvaluator($emp, $checkpointId)) {
            return true;
        }

        return false;
    }

    /**
     * Check employee has permission view checkpoint detail
     * @param Employee $emp
     * @param CheckpointResult $result
     * @return boolean
     */
    public function isAllowDetail($emp, $result)
    {
        $teamIds = self::getArrTeamIdByEmployee($emp->id);
        $checkpoint = Checkpoint::getCheckpointById($result->checkpoint_id);
        
        if (Permission::getInstance()->isScopeCompany(null, 'team::checkpoint.checkpointdetail')
            || (Permission::getInstance()->isScopeTeam(null, 'team::checkpoint.checkpointdetail') && in_array($checkpoint->team_id, $teamIds)) ) {
            return true;
        }

        if ($this->isLeader($emp, $result->checkpoint_id) 
                || $this->isRelate($emp, $result->checkpoint_id)
                || $this->isEvaluatorOfResult($emp, $result)
                || $emp->id == $result->employee_id) {
            return true;
        }

        return false;
    }

    /**
     * Check is leader of checkpoint's team
     * @param type $emp
     * @param type $checkpointId
     * @return type
     */
    public function isLeader($emp, $checkpointId)
    {
        $checkpoint = Checkpoint::getCheckpointById($checkpointId); 
        $team = Team::find($checkpoint->team_id);
        if ($team->leader_id) {
            return ($emp->id == $team->leader_id);
        }
        return false;
    }

    /**
     * Check is relate person of checkpoint
     * @param type $emp
     * @param type $checkpointId
     * @return boolean
     */
    public function isRelate($emp, $checkpointId)
    {
        $checkpoint = Checkpoint::getCheckpointById($checkpointId); 
        $emails = [];
        if ($checkpoint->rikker_relate && !empty($checkpoint->rikker_relate)) {
            $emails = explode(',', $checkpoint->rikker_relate); 
        }

        if (count($emails)) {
            if (in_array($emp->email, $emails)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find evaluator of evaluated
     * @param Checkpoint $checkpoint
     * @param Employee $emp
     * @return Employee|null
     */
    public function findEvaluator($checkpoint, $emp)
    {
        if ($checkpoint->evaluators && !empty($checkpoint->evaluators)) {
            $evaluators = json_decode($checkpoint->evaluators);
            foreach ($evaluators as $item) {
                foreach ($item->evaluated as $evaluatedItem) {
                    if ($evaluatedItem == $emp->id) {
                        $evaluator = Employee::getEmpById($item->evaluatorId);
                        return $evaluator;
                    }
                }
            }
            return null;
        }
        return null;
    }

    /**
     * Get evaluators of checkpoint
     * @param int $checkpointId
     * @return Employee collection|null
     */
    public function getEvaluatorsOfCheckpoint($checkpoint)
    {
        if ($checkpoint->evaluator_id && !empty($checkpoint->evaluator_id)) {
            $evaluatorIds = explode(',', $checkpoint->evaluator_id);
            if (count($evaluatorIds)) {
                return Employee::getEmpByIds($evaluatorIds, 'email');
            }
            return null;
        }
        return null;
    }

    /**
     * Check is evaluator of checkpoint
     * @param type $emp
     * @param type $checkpointId
     * @return boolean
     */
    public function isEvaluator($emp, $checkpointId)
    {
        $checkpoint = Checkpoint::getCheckpointById($checkpointId); 

        if ($checkpoint->evaluators && !empty($checkpoint->evaluators)) {
            $evaluators = json_decode($checkpoint->evaluators);
            foreach ($evaluators as $item) {
                if ($emp->id == $item->evaluatorId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check is Evaluator of checkpoint result
     * @param Employee $emp current user login
     * @param CheckpointResult $result current checkoint result
     * @return boolean
     */
    public function isEvaluatorOfResult($emp, $result)
    {
        $checkpoint = Checkpoint::getCheckpointById($result->checkpoint_id);
        if ($checkpoint->evaluators && !empty($checkpoint->evaluators)) {
            $evaluator = $this->findEvalutor($checkpoint->evaluators, $result->employee_id);
            if ($evaluator && $emp->id == $evaluator->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check employee is evaluator of this checkpoint
     * @param Employee $emp current emp
     * @param int $checkpointId
     * @return boolean
     */
    public function canEdit($emp, $evaluator)
    {
        return ($emp->id == $evaluator->id);
    }

    /**
     * Get checkpoint list by permission
     *
     * @param string $order
     * @param string $dir
     * @return list Checkpoint
     */
    public function getList($order, $dir)
    {
        $emp = Permission::getInstance()->getEmployee();
        $model = new Checkpoint();
        $per = new Permission();
        if (Permission::getInstance()->isScopeCompany()) {
            $list = $model->getList($order, $dir);
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamIds = self::getArrTeamIdByEmployee($emp->id);
            $list = $model->getList($order, $dir, null, $teamIds);
        } else {
            $list = $model->getList($order, $dir, $emp->id);
        }

        return $list;
    }

    /**
     * Check employee of team
     *
     * @param Employee $emp
     * @param string $toke
     * @param int $id
     * @return boolean
     */
    public function checkTeam($emp, $token, $id)
    {
        $teamIds = self::getArrTeamIdByEmployee($emp->id);
        $checkpointModel = new Checkpoint();
        $checkpoint = $checkpointModel->getCheckpointByIdAndToken($id,$token);
        return in_array($checkpoint->team_id, $teamIds);
    }

    /**
     * Get team child list by employee
     * @param int $employeeId
     * @return array teamId
     */
    public static function getArrTeamIdByEmployee($employeeId)
    {
        $teamMembersModel = new TeamMember();
        $teamMembers = $teamMembersModel->getTeamMembersByEmployee($employeeId);

        //get teams of current user
        $arrTeamIdTemp = [];
        foreach ($teamMembers as $item) {
            $arrTeamIdTemp[] = self::getTeamChild($item->team_id);
        }

        $arrTeamId = [];
        for ($i=0; $i<count($arrTeamIdTemp); $i++) {
            for ($j=0; $j<count($arrTeamIdTemp[$i]); $j++) {
                $arrTeamId[] = $arrTeamIdTemp[$i][$j];
            }
        }
        return $arrTeamId;
    }

    /**
     * Get team child list by teamId
     * @param int $teamId
     * @return array teamId
     */
    public static function getTeamChild($teamId)
    {
        $arrTeamId = [];
        $arrTeamId[] = $teamId;
        $model = new Team();
        $teamChilds = $model->getTeamByParentIdNoTrashed($teamId);

        if (count($teamChilds)) {
            foreach ($teamChilds as $child) {
                $arrTeamId[] = $child->id;
                if (!CssPermission::isTeamChildLowest($child->id)) {
                    $childs = self::getTeamChild($child->id);
                    $count = count($childs);
                    for ($i=0; $i<$count; $i++) {
                        $arrTeamId[] = $childs[$i];
                    }
                }
            }
        }
        return $arrTeamId;
    }

    /**
     * Find evaluator from evaluated
     * @param string $evaluators
     * @param int $evaluatedId 
     * @return object|null
     */
    public function findEvalutor($evaluators, $evaluatedId)
    {
        $evaluator = null;
        if ($evaluators) {
            $evaluators = json_decode($evaluators);
            foreach ($evaluators as $item) {
                //Find evaluator of current user
                if (in_array($evaluatedId, $item->evaluated)) {
                    $evaluator = Employee::getEmpById($item->evaluatorId);
                }
            }
        }

        return $evaluator;
    }

    /**
     * Get list evaluated of evaluator
     * @param object $evaluators
     * @param int $evaluatorId
     * @return array|null
     */
    public function getEvaluatedByEvaluator($evaluators, $evaluatorId)
    {
        if ($evaluators) {
            $evaluators = json_decode($evaluators);
            foreach ($evaluators as $item) {
                if ($item->evaluatorId == $evaluatorId) {
                    return $item->evaluated;
                }
            }
            return null;
        }
        return null;
    }

    /**
     * Get count evaluated of checkpoint
     * @param $evaluatedId
     * @return int
     */
    public function getCountEvaluatedOfCheckpoint($evaluatedId)
    {
        $evaluatedIds = explode(',', $evaluatedId);
        if (is_array($evaluatedIds)) {
            return count($evaluatedIds);
        }
        return 0;
    }

    /**
     * get nickname from email
     *
     * @return string
     */
    public static function getNickName($email)
    {
        return preg_replace('/@.*/', '', $email);
    }

    /**
     * Singleton instance
     *
     * @return \Rikkei\Team\View\CheckpointPermission
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Get Class of checkpoint button
     * @param int $rank
     * @param int $empPoint
     * @param int $leaderPoint
     * @return string
     */
    public static function getClassButton($rank, $empPoint, $leaderPoint)
    {
        $class = 'btn-default';
        if ($rank == $empPoint) {
            $class .= ' btn-primary';
            if ($rank == $leaderPoint || empty($leaderPoint)) {
                $class .= ' point-rank';
            }
        } elseif ($rank == $leaderPoint) {
            $class .= ' btn-danger point-rank';
        } else {
            $class .= '';
        }
        return $class;
    }

    /**
     * Check employee has permission make checkpoint
     * Return true when have not made OR leader have not reviewed
     *
     * @param int $checkpointId
     * @param int $empId
     * @return boolean
     */
    public static function hasUpdateCheckpoint($checkpointId, $empId)
    {
        $resultOfEmp = CheckpointResult::getResultOfEmployee($empId, $checkpointId);
        return empty($resultOfEmp) || empty($resultOfEmp->leader_total_point);
    }

    /**
     * Add checkpoint result deital to $cate
     *
     * @param CheckpointResult $result
     * @param int $empId
     * @param array $cate
     * @return array
     */
    public static function getQuestionWithPoint($result, $empId, $cate)
    {
        foreach ($cate as &$item) {
            if ($item['cateChild']) {
                foreach ($item['cateChild'] as &$itemChild) {
                    if ($itemChild['questionsChild']) {
                        foreach ($itemChild['questionsChild'] as &$questionChild) {
                            $detail = CheckpointResultDetail::getDetail($result->id, $questionChild->id);
                            if ($detail) {
                                $questionChild->point = $detail->point;
                                $questionChild->comment = $detail->comment;
                                $questionChild->leader_point = $detail->leader_point;
                                $questionChild->leader_comment = $detail->leader_comment;
                            }
                        }
                    }
                }
            } elseif ($item['questions']) {
                foreach ($item['questions'] as &$question) {
                    $detail = CheckpointResultDetail::getDetail($result->id, $question->id);
                    if ($detail) {
                        $question->point = $detail->point;
                        $question->comment = $detail->comment;
                        $question->leader_point = $detail->leader_point;
                        $question->leader_comment = $detail->leader_comment;
                    }
                }
            } else {
                throwException('do not any thing');
            }
        }
        return $cate;
    }

    /**
     * Get tooltip of questions in checkpoint detail page
     *
     * @param CheckpointQuestion $question
     * @param CheckTime $checkTime
     *
     * @return string
     */
    public static function getTooltipCheckpoint($question, $checkTime)
    {
        $specialContent = [
            'Số man month làm trong 6 tháng trước',
            'Giải thưởng cá nhân tại Rikkei',
        ];

        $dateMonthArray = explode('/', $checkTime->check_time);
        $month = $dateMonthArray[0];
        $year = $dateMonthArray[1];
        $time = Carbon::createFromDate($year, $month)->startOfMonth();
        $monthTo = $time->modify('-1 months')->endOfMonth()->format('d/m/Y');
        $monthFrom = $time->modify('-5 months')->startOfMonth()->format('d/m/Y');

        if (in_array($question->content, $specialContent)) {
            return Lang::get("team::view.$question->tooltip", ['from' => $monthFrom, 'to' => $monthTo]);
        }

        return $question->tooltip;
    }
}
