<?php

namespace Rikkei\Project\View;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\SourceServer;
use Rikkei\Project\Model\StageAndMilestone;
use DateTime;
use Carbon\Carbon;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Project\Model\Project;

class ValidatorExtend
{
    protected $arrayStatusDelete = [ProjectMember::STATUS_SUBMMITED_DELETE,
                                    ProjectMember::STATUS_REVIEWED_DELETE,
                                    ProjectMember::STATUS_FEEDBACK_DELETE,
                                    ProjectMember::STATUS_DELETE,
                                    ProjectMember::STATUS_DELETE_APPROVED,
                                    ProjectMember::STATUS_DELETE_DRAFT_EDIT,
                                    ProjectMember::STATUS_DELETE_DRAFT,
                                    ProjectMember::STATUS_DELETE_FEEDBACK_EDIT,
                                    ProjectMember::STATUS_DELETE_FEEDBACK,
                                    ProjectMember::STATUS_DRAFT_DELETE];
    /**
     * add validator after or equal date
     */
    public static function addAfterEqual()
    {
        Validator::extend('after_equal', function($attribute, $value, $parameters) {
            return strtotime(Input::get($parameters[0])) <= strtotime($value);
        });
    }
    
    /**
     * add validator after or equal date
     */
    public static function addAfterEqualValue()
    {
        Validator::extend('after_equal_value', function($attribute, $value, $parameters) {
            return strtotime($parameters[0]) <= strtotime($value);
        });
    }
    
    /**
     * add validator of wo
     */
    public static function addWO()
    {
        $class = get_class();
        Validator::extend('project_member', "{$class}@validateProjectMember");
        Validator::extend('before_or_equal', "{$class}@validateBeforeOrEqual");
        Validator::extend('team_allocation', "{$class}@validateTeamAllocation");
        Validator::extend('type_project_member', "{$class}@validateTypeProjectMember");
        Validator::extend('source_servers', "{$class}@validateSourceServers");
        Validator::extend('exits_stage', "{$class}@validateExitsStage");
        Validator::extend('betwwen_time_project', "{$class}@validateBetwwenTimeProject");
        Validator::extend('greater_than', "{$class}@validateGreaterThan");
        Validator::extend('stage_milestone', "{$class}@validateStageMilestone");
        Validator::extend('add_stage_milestone', "{$class}@validateAddStageMilestone");
        Validator::extend('effort_value', "{$class}@validateEffortValue");
        Validator::extend('proj_member_prog_langs', "{$class}@validateProjMemberProgLangs");
    }
    
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
      $arrayIdMemberHasDraft = ProjectMember::whereNotNull('parent_id')
                                              ->where('project_id', $parameters[1])
                                              ->lists('parent_id')->toArray();
      $item = ProjectMember::where('employee_id', $parameters[0])
                           ->where('project_id', $parameters[1])
                           ->whereDate('start_at', '=', $parameters[2])
                           ->whereDate('end_at', '=', $parameters[3])
                           ->where('type', $parameters[4])
                           ->whereNotIn('id', $arrayIdMemberHasDraft)
                           ->whereNotIn('status', $this->arrayStatusDelete);

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
            if (isset($parameters[1])) {
                if ($stage->parent_id == isset($parameters[1])) {
                    continue;
                }
            }
            $checkError = false;
            break;
        }
        return $checkError; 
    }

    /**
     * validate effort value
     * @param   $attribute  
     * @param   $value      
     * @param   $parameters 
     * @return  boolean          
     */
    public function validateEffortValue($attribute, $value, $parameters)
    {
        /*$arrayIdMemberHasDraft = ProjectMember::whereNotNull('parent_id')
            ->where('project_id', $parameters[2])
            ->lists('parent_id')->toArray();
        $items = ProjectMember::where('employee_id', $parameters[1])
            ->where('project_id', $parameters[2])
            ->whereNotIn('id', $arrayIdMemberHasDraft)
            ->whereNotIn('status', $this->arrayStatusDelete);
         */
        // find all object same employee, if item has child, get child replace parent
        $maxEffort = Project::MAX_EFFORT;
        if ($parameters[0] > $maxEffort) {
          return false;
        }
        $startInput = Carbon::parse($parameters[3]);
        $endInput = Carbon::parse($parameters[4]);
        $items = ProjectMember::select(['effort', 'start_at', 'end_at'])
            ->where('employee_id', $parameters[1])
            ->where('project_id', $parameters[2])
            ->whereNotIn('status', $this->arrayStatusDelete)
                // find child item if exists
            ->whereNotIn('id', function ($query) use ($parameters) {
                $query->select('parent_id')
                    ->from(ProjectMember::getTableName() . ' as tmp_proj_member')
                    ->whereNotNull('parent_id')
                    ->where('project_id', $parameters[2])
                    ->where('employee_id', $parameters[1]);
            })
                // find item start date end end date identical
            ->where(function($query) use ($startInput, $endInput) {
                $query->orWhere(function($query) use ($startInput) {
                    $query->where('start_at', '<=', $startInput->format('Y-m-d'))
                        ->where('end_at', '>=', $startInput->format('Y-m-d'));
                })
                ->orWhere(function($query) use ($endInput) {
                    $query->where('start_at', '<=', $endInput->format('Y-m-d'))
                        ->where('end_at', '>=', $endInput->format('Y-m-d'));
                })
                ->orWhere(function($query) use ($startInput, $endInput) {
                    $query->where('start_at', '>=', $startInput->format('Y-m-d'))
                        ->where('end_at', '<=', $endInput->format('Y-m-d'));
                });
            });
        if (isset($parameters[5])) {
            $items = $items->whereNotIn('id', [$parameters[5]]);
        }
        $items = $items->get();
        if (!count($items) > 0) {
            return true;
        }
        if ($endInput >= $startInput) {
            $countItem = count($items);
            for ($i = 0 ; $i <= $countItem - 1; $i++) {
                if (!$items->get($i)) {
                    continue;
                }
                $sumEffort = 0;
                $startDateI = strtotime($items->get($i)->start_at);
                $endDateI = strtotime($items->get($i)->end_at);
                // compare with each item, if same time, + effort
                for ($j = $i + 1 ; $j <= $countItem - 1; $j++) {
                    if (!$items->get($j)) {
                        continue;
                    }
                    $startDateJ = strtotime($items->get($j)->start_at);
                    $endDateJ = strtotime($items->get($j)->end_at);

                    if(($startDateI <= $startDateJ && $startDateJ <= $endDateI) ||
                        ($startDateI <= $endDateJ && $endDateJ <= $endDateI) ||
                        ($startDateJ <= $startDateI && $endDateI <= $endDateJ)
                    ) {
                        $sumEffort += $items->get($j)->effort;
                    }
                }
                $sumEffort += $items->get($i)->effort + $parameters[0];
                if ($sumEffort > $maxEffort) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * check programing of project include programing of member
     * 
     * @param string $attribute
     * @param array $value
     * @param array $parameters
     * @return boolean
     */
    public function validateProjMemberProgLangs($attribute, $value, $parameters)
    {
        if (!isset($parameters[0])) {
            return false;
        }
        if (!$value || !count($value)) {
            return true;
        }
        $projectId = $parameters[0];
        return ProjectProgramLang::isIncludeProgramIds($projectId, 
            (array) $value);
    }
}