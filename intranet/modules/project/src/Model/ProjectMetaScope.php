<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

class ProjectMetaScope extends CoreModel
{
    protected $table = 'project_meta_scope';
    protected $fillable = ['scope_scope', 'project_metas_id'];

    public static function getProjectMetaScope($id)
    {
        return self::where('project_metas_id', $id)
            ->whereNull('deleted_at')
            ->select(DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(scope_scope)) SEPARATOR ",") as scope'))
            ->first();
    }

    public static function getLabelScope($id)
    {
        $scope = self::getProjectMetaScope($id);
        $scopeLabelAll = ProjectScope::labelScope();
        $labelScope = [];
        foreach(explode(',', $scope->scope) as $items => $val) {
            if (in_array($val, ProjectScope::getAllScope())) {
                $labelScope[$items] = $scopeLabelAll[$val];
            } else {
                continue;
            }
        }
        return $labelScope;
    }
}