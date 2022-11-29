<?php

namespace Rikkei\Tag\View;

use Rikkei\Tag\Model\Field;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Tag\Model\ProjectTag;
use Rikkei\Core\Model\CoreConfigData;

class TagGeneral
{
    /**
     * get all id of children of field
     * 
     * @param model $field
     * @return array
     */
    public static function getFieldIdsChildren($field)
    {
        $result = [];
        self::getFieldIdsChildrenRecursive(
                $field->id, 
                Field::getFieldPath($field->set), 
                $result
        );
        $result[] = $field->id;
        return $result;
    }
    
    /**
     * get all id of children of field recursive 
     * 
     * @param int $id
     * @param array $tree
     * @param array $result
     * @return boolean
     */
    protected static function getFieldIdsChildrenRecursive($id, $tree, &$result)
    {
        if (!isset($tree[$id])) {
            return true;
        }
        if (!count($tree[$id]['child'])) {
            return true;
        }
        foreach ($tree[$id]['child'] as $itemChildId) {
            self::getFieldIdsChildrenRecursive($itemChildId, $tree, $result);
        }
        $result = array_merge($result, $tree[$id]['child']);
    }
    
    /**
     * convert item of array empty to null
     * 
     * @param array $array
     * @return array
     */
    public static function arrayEmptyToNull(array $array)
    {
        if (!count($array)) {
            return $array;
        }
        foreach ($array as $key => $item) {
            if (empty($item)) {
                $array[$key] = null;
            }
        }
        return $array;
    }
    
    /**
     * get values from attributes of model
     * 
     * @param model $item
     * @param array $attrs
     * @return arryay
     */
    public static function getValueFromAttr($item, $attrs) 
    {
        $result = [];
        foreach ($attrs as $attr) {
            $result[$attr] = $item->{$attr};
        }
        return $result;
    }
    
    /**
     * get list action
     * @return array
     */
    public static function projTagActions() {
        $actions = [];
        if (Permission::getInstance()->isAllow(TagConst::ROUTE_SUBMIT_PROJ_TAG)) {
            $actions[TagConst::ACTION_SUBMIT] = 'Submit';
        }
        if (Permission::getInstance()->isAllow(TagConst::ROUTE_APPROVE_PROJ_TAG)) {
            $actions[TagConst::ACTION_ASSIGN] = 'Assign';
            $actions[TagConst::ACTION_APPROVE] = 'Approve';
        }
        return $actions;
    }
    
    /**
     * can do action with project
     * @param type $projectId
     * @param type $scopeRoute
     * @return boolean
     */
    public static function canDoProject($projectId, $scopeRoute = null, $action = null) {
        $scope = Permission::getInstance();
        $currentUser = $scope->getEmployee();
        if (!$scope->isAllow($scopeRoute)) {
            return false;
        }
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            return true;
        }
        // check perssion team submit, approve tag
        if ($scope->isScopeTeam(null, $scopeRoute)) {
            $checkProject = TeamProject::whereIn('team_id', function ($query) use ($currentUser) {
                    $query->select('team_id')
                        ->from(TeamMember::getTableName())
                        ->where('employee_id', $currentUser->id);
                })
                ->where('project_id', $projectId)
                ->first();
            if ($checkProject) {
                return true;
            } else {
                $project = ProjectTag::find($projectId, ['leader_id', 'tag_assignee']);
                if (!$project) {
                    return false;
                }
                if ($action == TagConst::ACTION_SUBMIT) {
                    return $project->tag_assignee == $currentUser->id;
                } elseif (in_array($action, [TagConst::ACTION_ASSIGN, TagConst::ACTION_APPROVE])) {
                    return true;
                }
            }
        }
        
        if (!isset($project)) {
            $project = ProjectTag::find($projectId, ['leader_id', 'tag_assignee']);
        }
        if (!$project) {
            return false;
        }
        if (in_array($action, [TagConst::ACTION_ASSIGN, $action == TagConst::ACTION_APPROVE])) {
            // permission leader
            return $project->leader_id == $currentUser->id;
        }
        // permission pm or leader
        return in_array($currentUser->id, [
            $project->tag_assignee,
            $project->leader_id
        ]);
    }
    
    /**
     * check permission edit old project
     * 
     * @param object $project
     * @return boolean
     */
    public static function isAllowEditOldProj($project)
    {
        $permission = Permission::getInstance();
        if ($permission->isScopeNone(null, TagConst::RA_PROJ_OLD_EDIT)) {
            return false;
        }
        if ($permission->isScopeCompany(null, TagConst::RA_PROJ_OLD_EDIT)) {
            return true;
        }
        if (count(array_intersect($project->getTeamIds(), $permission->getTeams()))) {
            return true;
        }
        if ($permission->getEmployee() && 
            in_array($permission->getEmployee()->id, [$project->leader_id, $project->manager_id])
        ) {
            return true;
        }
        return false;
    }
    
    /**
     * increment version of ldb version
     */
    public static function incrementLDBVersion()
    {
        $item = CoreConfigData::getItem(TagConst::KEY_CONFIT_LDB_VERSION);
        $value = (int) $item->value;
        if ($value > 9e100) {
            $value = 1;
        } else {
            $value++;
        }
        $item->value = $value;
        $item->save();
    }
    
    /**
     * increment config data tag -> update storage on client
     */
    public static function incrementConfigTagVersion() {
        $maxVer = 1000;
        $tagVer = CoreConfigData::getItem(TagConst::KEY_TAG_VER);
        $version = 1;
        if ($tagVer->value) {
            $oldVer = intval($tagVer->value);
            if ($oldVer > $maxVer) {
                $oldVer = 1;
            }
            $version = $oldVer + 1;
        }
        $tagVer->value = $version;
        $tagVer->save();
    }
}