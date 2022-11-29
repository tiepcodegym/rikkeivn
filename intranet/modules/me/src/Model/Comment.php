<?php

namespace Rikkei\Me\Model;

use Rikkei\Project\Model\MeComment;
use Rikkei\Me\Model\Attribute as MeAttribute;
use Illuminate\Support\Facades\DB;

class Comment extends MeComment
{
    private static $instance = null;

    /**
     * get instance of this class
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * get comment by evaluation and attribute
     * @param integer $evalId
     * @param integer|null $attrId
     * @return collection
     */
    public function getComments($evalId, $attrId = null)
    {
        return parent::getByEvalAttr($evalId, $attrId);
    }

    /*
     * get comment class foreach evaluations
     */
    public function getEvalCommentClass($evalIds = [], $sepBy = 'attr')
    {
        $collect = self::select(
                'eval_id',
                DB::raw('IFNULL(attr_id, -1) as attr_id'),
                'type'
            )
            ->whereIn('eval_id', $evalIds)
            ->get();
        if ($collect->isEmpty()) {
            return [];
        }

        $results = [];
        if ($sepBy == 'attr') {
            foreach ($collect as $item) {
                if (!isset($results[$item->eval_id])) {
                    $results[$item->eval_id] = [];
                }
                if (!isset($results[$item->eval_id][$item->attr_id])) {
                    $results[$item->eval_id][$item->attr_id] = [];
                }
                $typeClass = 'td' . $item->type_class;
                if (!in_array($typeClass, $results[$item->eval_id][$item->attr_id])) {
                    if (!in_array('has_comment', $results[$item->eval_id][$item->attr_id])) {
                        $results[$item->eval_id][$item->attr_id][] = 'has_comment';
                    }
                    $results[$item->eval_id][$item->attr_id][] = $typeClass;
                }
            }
            return $results;
        }

        if ($sepBy == 'eval') {
            foreach ($collect as $item) {
                if (!isset($results[$item->eval_id])) {
                    $results[$item->eval_id] = [];
                }
                $typeClass = 'td' . $item->type_class;
                if (!in_array($typeClass, $results[$item->eval_id])) {
                    if (!in_array('has_comment', $results[$item->eval_id])) {
                        $results[$item->eval_id][] = 'has_comment';
                    }
                    $results[$item->eval_id][] = $typeClass;
                }
            }
            return $results;
        }
        return $results;
    }

    /*
     * list attributes commented by special user
     */
    public function listAttrsCommented($evalIds = [], $userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }
        $collect = self::select(
                'id',
                'eval_id',
                DB::raw('IFNULL(attr_id, -1) as attr_id'),
                'type'
            )
            ->whereIn('eval_id', $evalIds)
            ->where('employee_id', $userId)
            ->get();
        if ($collect->isEmpty()) {
            return [];
        }

        $results = [];
        foreach ($collect as $item) {
            if (!isset($results[$item->eval_id])) {
                $results[$item->eval_id] = [];
            }
            if (!isset($results[$item->eval_id][$item->attr_id])) {
                $results[$item->eval_id][$item->attr_id] = [];
            }
            if (!in_array($item->id, $results[$item->eval_id][$item->attr_id])) {
                $results[$item->eval_id][$item->attr_id][] = $item->id;
            }
        }
        return $results;
    }

    /*
     * get comment by eval ID
     */
    public function getEvalComments($evalId)
    {
        return self::select(
                'cm.content',
                'cm.type',
                'cm.comment_type',
                'cm.created_at',
                'user.employee_id',
                'user.avatar_url',
                'epl.name',
                'epl.email',
                'attr.label as attr_label'
            )
            ->from(self::getTableName() . ' as cm')
            ->join(\Rikkei\Team\Model\Employee::getTableName() . ' as epl', 'cm.employee_id', '=', 'epl.id')
            ->leftJoin(\Rikkei\Core\Model\User::getTableName() . ' as user', 'cm.employee_id', '=', 'user.employee_id')
            ->leftJoin(\Rikkei\Project\Model\MeAttributeLang::getTableName() . ' as attr', function ($join) {
                $join->on('cm.attr_id', '=', 'attr.attr_id')
                    ->where('attr.lang_code', '=', app()->getLocale());
            })
            ->where('cm.eval_id', $evalId)
            ->groupBy('cm.id')
            ->get();
    }

    /*
     * insert comment late time
     */
    public function insertLateTime($evalItem, $lateTime)
    {
        $attrId = MeAttribute::getInstance()->getAttrIdByType(MeAttribute::TYPE_NEW_REGULATIONS);
        $comment = self::where('eval_id', $evalItem->id)
                ->where('type', self::TYPE_LATE_TIME)
                ->where('attr_id', $attrId)
                ->first();
        if (!$comment) {
            if ($lateTime > 0) {
                $comment = self::create([
                    'eval_id' => $evalItem->id,
                    'type' => self::TYPE_LATE_TIME,
                    'attr_id' => $attrId,
                    'employee_id' => $evalItem->employee_id,
                    'content' => trans('me::view.Late time', ['time' => $lateTime])
                ]);
            }
        } else {
            if ($lateTime > 0) {
                $comment->update([
                    'content' => trans('me::view.Late time', ['time' => $lateTime]),
                ]);
            } else {
                $comment->delete();
            }
        }
    }
}
