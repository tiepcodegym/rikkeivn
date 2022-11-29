<?php 
namespace Rikkei\Project\Services;

use Illuminate\Validation\Validator;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Project\Model\StageAndMilestone;
use DateTime;

class CustomValidator extends Validator
{
    /**
     * validate project memeber
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateProjectMember($attribute, $value, $parameters)
    {
        $projectMember = ProjectMember::find($parameters[1]);
        $projectMemberParent = ProjectMember::find($projectMember->parent_id);
        if ($projectMember->employee_id == $projectMemberParent->employee_id) {
            return true;
        } else {
            $countApproved  = ProjectMember::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->whereNotIn('id' ,[$parameters[1]])
                                      ->where('status', '!=', ProjectMember::STATUS_APPROVED)
                                      ->count();
            $countNotApproved = ProjectMember::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->where('status', ProjectMember::STATUS_APPROVED)
                                      ->count();
            if (!$countApproved || $countNotApproved == 1) {
                return true;
            }
            return false;
        }
    }

    /**
     * validate source server
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean    
     */
    public function validateSourceServers($attribute, $value, $parameters)
    {
      $query = SourceServer::where($attribute, $value);
      if (isset($parameters[0])) {
          $sourceServer = SourceServer::getSourceServer($parameters[0]);
          $sourceServerDraft = SourceServer::getSourceServerDraft($parameters[0]);
          // add new
          if (!$sourceServer && !$sourceServerDraft) {
              $isCheck = $query->get();
              if (count($isCheck)) {
                  return false;
              }
          // update draft (feedback)       
          } else if (!$sourceServer && $sourceServerDraft) {
              $isCheck = $query->whereNotIn('id', [$sourceServerDraft->id])->get();
              if (count($isCheck)) {
                  return false;
              }
          // update draft for edit approved
          } else if ($sourceServer && $sourceServerDraft) {
              $isCheck = $query->whereNotIn('id', [$sourceServer->id, $sourceServerDraft->id])->get();
              if (count($isCheck)) {
                  return false;
              }
          //add draft for edit approved                        
          } else {
              $isCheck = $query->whereNotIn('id', [$sourceServer->id])->get();
              if (count($isCheck)) {
                  return false;
              }
          }
      } else {
          $isCheck = $query->get();
          if (count($isCheck)) {
              return false;
          }
      }
      return true;
    }

    /**
     * validate team allocation
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean    
     * 
     */
    public function validateTeamAllocation($attribute, $value, $parameters)
    {
      $arrayStatusDelete = [ProjectMember::STATUS_SUBMMITED_DELETE,
                            ProjectMember::STATUS_REVIEWED_DELETE,
                            ProjectMember::STATUS_FEEDBACK_DELETE,
                            ProjectMember::STATUS_DELETE,
                            ProjectMember::STATUS_DELETE_APPROVED,
                            ProjectMember::STATUS_DELETE_DRAFT_EDIT,
                            ProjectMember::STATUS_DELETE_DRAFT,
                            ProjectMember::STATUS_DELETE_FEEDBACK_EDIT,
                            ProjectMember::STATUS_DELETE_FEEDBACK,
                            ProjectMember::STATUS_DRAFT_DELETE];
      $arrayIdMemberHasDraft = ProjectMember::whereNotNull('parent_id')
                                              ->where('project_id', $parameters[1])
                                              ->lists('parent_id')->toArray();
      $item = ProjectMember::where('employee_id', $parameters[0])
                           ->where('project_id', $parameters[1])
                           ->whereDate('start_at', '=', $parameters[2])
                           ->whereDate('end_at', '=', $parameters[3])
                           ->where('type', $parameters[4])
                           ->whereNotIn('id', $arrayIdMemberHasDraft)
                           ->whereNotIn('status', $arrayStatusDelete);

      if (!isset($parameters[5]) && !isset($parameters[6])) {
        $items = $item->count();
      } else {
        if (isset($parameters[5]) && !isset($parameters[6])) {
          $items = $item->whereNotIn('id', [$parameters[5]])
                        ->count();
        } else {
          $items = $item->whereNotIn('id', [$parameters[5], $parameters[6]])
                        ->count();
        }
      }
      if ($items > 0) {
        return false;
      }
      return true;
    }

    /**
     * validate exits stage
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean    
     * 
     */
    public function validateExitsStage($attribute, $value, $parameters)
    {
      if (!$parameters[1]) {
        if (!$value) {
          return true;
        }
      }
      $allStage = StageAndMilestone::getArrayStageOfProject($parameters[0]);
      if (in_array($value, $allStage)) {
        return true;
      }
      return false;
    }

    /*
     * validate start date before or equal end date
     * @param type $attribute
     * @param type $value
     * @param type $parameters
     * @return boolean
     */
    public function validateBeforeOrEqual($attribute, $value, $parameters)
    {
      if($parameters[1]) {
        $endDate = DateTime::createFromFormat('Y-m-d', $parameters[1]);
        $startDate = DateTime::createFromFormat('Y-m-d', $value);
        return ($startDate->getTimestamp() <= $endDate->getTimestamp());
      }
      return true;
    }

    /*
     * validate date betwwen time project
     * @param type $attribute
     * @param type $value
     * @param type $parameters
     * @return boolean
     */
    public function validateBetwwenTimeProject($attribute, $value, $parameters)
    {
      $time = DateTime::createFromFormat('Y-m-d', $value);
      $startDate = DateTime::createFromFormat('Y-m-d', $parameters[0]);
      $endDate = DateTime::createFromFormat('Y-m-d', $parameters[1]);
      if ($startDate->getTimestamp() <= $time->getTimestamp() && $time->getTimestamp() <= $endDate->getTimestamp()) {
        return true;
      }
      return false;
    }

    /*
     * validate type project memeber
     * @param type $attribute
     * @param type $value
     * @param type $parameters
     * @return boolean
     */
    public function validateTypeProjectMember($attribute, $value, $parameters)
    {
      if ($value == ProjectMember::TYPE_PM) {
        return ProjectMember::checkPMDraft($parameters, $value);
      }
      return true;
    }

    /*
     * validate value greater than
     * @param type $attribute
     * @param type $value
     * @param type $parameters
     * @return boolean
     */
    public function validateGreaterThan($attribute, $value, $parameters)
    {
      return $value > $parameters[0];
    }

    /**
     * validate stage milestone
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateStageMilestone($attribute, $value, $parameters)
    {
        $stage = StageAndMilestone::find($parameters[1]);
        $stageParent = StageAndMilestone::find($stage->parent_id);
        if ($stage->employee_id == $stageParent->employee_id) {
            return true;
        } else {
            $countApproved  = StageAndMilestone::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->whereNotIn('id' ,[$parameters[1]])
                                      ->where('status', '!=', StageAndMilestone::STATUS_APPROVED)
                                      ->count();
            $countNotApproved = StageAndMilestone::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->where('status', StageAndMilestone::STATUS_APPROVED)
                                      ->count();
            if (!$countApproved || $countNotApproved == 1) {
                return true;
            }
            return false;
        }
    }
     /**
     * validate add stage milestone
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateAddStageMilestone($attribute, $value, $parameters)
    {
      $arrayStatus = [StageAndMilestone::STATUS_DRAFT_DELETE,
                      StageAndMilestone::STATUS_SUBMMITED_DELETE,
                      StageAndMilestone::STATUS_REVIEWED_DELETE,
                      StageAndMilestone::STATUS_FEEDBACK_DELETE];
      $stages = StageAndMilestone::where('project_id', $parameters[0])
                                   ->where('stage', $value)
                                   ->whereNotIn('status', $arrayStatus);
      if (isset($parameters[1])) {
        $stages = $stages->whereNotIn('id', [$parameters[1]]);
      }
      $stages = $stages->get();
      if (!count($stages)) {
        return true;
      }
      $checkError = true;
      foreach ($stages as $key => $stage) {
        if($stage->projectStageAndMilestoneChild) {
          continue;
        }
        $checkError = false;
        break;
      }
      return $checkError; 
    }
}