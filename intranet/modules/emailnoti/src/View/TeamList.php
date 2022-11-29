<?php

namespace Rikkei\Emailnoti\View;

use Rikkei\Team\Model\Team;
use Lang;

class TeamList
{
    /**
     * Team List tree
     * 
     * @return type
     */
    public static function getTreeHtml($idActive = null, $teamIds = [])
    {
        $html = '<ul class="" style="list-style:none">';
        $html .= self::getTreeDataRecursive(null, 0, $idActive, $teamIds);
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * get team tree option recursive
     * 
     * @param int $id
     * @param int $level
     */
    protected static function getTreeDataRecursive($parentId = null, $level = 0, $idActive = null, $teamIds = [])
    {
        $teamList = Team::select('id', 'name', 'parent_id')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc')
                ->get();
        $countCollection = count($teamList);
        if (!$countCollection) {
            return;
        }
        $html = '';
        $i = 0;
        foreach ($teamList as $team) {
            $classLi = '';
            $classLabel = 'team-item';
            $optionA = " data-id=\"{$team->id}\"";
            $classA = '';
            if ($i == $countCollection - 1) {
                $classLi = 'last';
            }
            if ($team->id == $idActive) {
                $classA .= 'active';
            }
            $classLi = $classLi ? " class=\"{$classLi}\"" : '';
            $classLabel = $classLabel ? " class=\"{$classLabel}\"" : '';
            $classA = $classA ? " class=\"{$classA}\"" : '';
            $checked = in_array($team->id, $teamIds) ? 'checked' : '';
            $html .= "<li>";
            $html .= "<label>";
            $html .= "<input type='checkbox' value='$team->id' $checked name='team'> ";
            $html .= "<span>";
            $html .= $team->name;
            $html .= '</span>';
            $html .= "</label>";
            $htmlChild = self::getTreeDataRecursive($team->id, $level + 1, $idActive, $teamIds);
            if ($htmlChild) {
                $html .= '<ul style="list-style:none">';
                $html .= $htmlChild;
                $html .= '</ul>';
            }
            $html .= '</li>';
        }

        return $html;
    }    
}
