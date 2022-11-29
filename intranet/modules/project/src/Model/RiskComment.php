<?php

namespace Rikkei\Project\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Lang;

class RiskComment extends CoreModel
{
    protected $table = 'risk_comments';

    const TYPE_ISSUE = 1;
    const TYPE_RISK = 2;

    public static function getComments($objId, $type)
    {
        return self::where('risk_comments.obj_id', $objId)
            ->where('risk_comments.type', $type)
            ->leftJoin('employees as e', 'e.id', '=', 'risk_comments.created_by')
            ->leftJoin('risk_attachs', function ($join) {
                $join->on('risk_attachs.obj_id', '=', 'risk_comments.id')
                    ->whereNull('risk_attachs.deleted_at');
            })
            ->select([
                'risk_comments.*',
                'e.name',
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(risk_attachs.path, "*", risk_attachs.id)) SEPARATOR ",") as paths')
            ])
            ->groupBy('risk_comments.id')
            ->orderBy('risk_comments.id', 'desc')
            ->get();
    }

    public static function getMentions($content)
    {
        global $DB;
        $mention_regex = '/@\[([0-9]+)\]/i'; //mention regrex to get all @texts

        if (preg_match_all($mention_regex, $content, $matches))
        {
            foreach ($matches[1] as $match)
            {
                $match_user = $DB->row("SELECT * FROM employees WHERE id=?",array($match));

                $match_search = '@[' . $match . ']';
                $match_replace = '<a target="_blank" href="">@' . $match_user['name'] . '</a>';

                if (isset($match_user['id']))
                {
                    $content = str_replace($match_search, $match_replace, $content);
                }
            }
        }
        return $content;
    }
}
