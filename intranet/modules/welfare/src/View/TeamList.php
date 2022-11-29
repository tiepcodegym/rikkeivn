<?php
namespace Rikkei\Welfare\View;

use Rikkei\Team\Model\Team;
use Lang;

class TeamList
{
    /**
     * Team List tree
     *
     * @return type
     */
    public static function getTreeHtml($idActive = null)
    {
        $html = '<ul class="treeview team-tree">';
        $html .= self::getTreeDataRecursive(null, 0, $idActive);
        $html .= '</ul>';
        return $html;
    }

    /**
     * get team tree option recursive
     *
     * @param int $id
     * @param int $level
     */
    protected static function getTreeDataRecursive($parentId = null, $level = 0, $idActive = null)
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

            $hrefA = route('welfare::team.member.index', ['id' => $team->id]);
            $html .= "<li{$classLi}>";
            $html .= "<label{$classLabel}>";
            $html .= "<a href=\"{$hrefA}\"{$classA}{$optionA} level='$level'>";
            $html .= $team->name;
            $html .= '</a>';
            $html .= '</label>';
            $htmlChild = self::getTreeDataRecursive($team->id, $level + 1, $idActive);
            if ($html) {
                $html .= '<ul>';
                $html .= $htmlChild;
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
}
