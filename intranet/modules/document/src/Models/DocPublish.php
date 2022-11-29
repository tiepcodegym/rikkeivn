<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\View\DocConst;
use Rikkei\Team\View\Permission;
use Rikkei\Document\Models\Document;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;

class DocPublish extends CoreModel
{
    protected $table = 'doc_publish';
    protected $fillable = ['doc_id', 'team_id', 'employee_id'];

    /**
     * insert data from array teamIds and accountIds
     * @param type $docId
     * @param type $teamIds
     * @param type $accountIds
     * @return type
     */
    public static function insertData($docId, $teamIds = [], $accountIds = [])
    {
        $dataInsert = [];
        if ($teamIds) {
            foreach ($teamIds as $teamId) {
                $dataInsert[] = [
                    'doc_id' => $docId,
                    'team_id' => $teamId,
                    'employee_id' => null
                ];
            }
        }
        if ($accountIds) {
            foreach ($accountIds as $accId) {
                $dataInsert[] = [
                    'doc_id' => $docId,
                    'team_id' => null,
                    'employee_id' => $accId
                ];
            }
        }
        //remove old data
        self::where('doc_id', $docId)->delete();
        if ($dataInsert) {
            return self::insert($dataInsert);
        }
        return null;
    }

    public static function getByDocId($docId)
    {
        return self::select('publish.team_id', 'team.name as team_name', 'publish.employee_id', 'emp.email as employee_email')
                ->from(self::getTableName() . ' as publish')
                ->leftJoin(Team::getTableName() . ' as team', 'publish.team_id', '=', 'team.id')
                ->leftJoin(Employee::getTableName() . ' as emp', 'publish.employee_id', '=', 'emp.id')
                ->where('publish.doc_id', $docId)
                ->groupBy('publish.id')
                ->get();
    }

    public static function permissView($doc)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        $currentUserId = $currentUser->id;
        if (\Rikkei\Document\View\DocConst::isPermissCompany()) {
            return true;
        }
        // Check publish all
        if ($doc->publish_all == DocConst::PUBLISH_ALL) {
            return true;
        }

        $teamIds = TeamMember::where('employee_id', $currentUserId)
                ->lists('team_id')
                ->toArray();
        $teamIds = Team::teamChildIds($teamIds);
        $hasPermiss = self::from(self::getTableName() . ' as publish')
                ->join(Document::getTableName() . ' as doc', 'publish.doc_id', '=', 'doc.id')
                ->leftJoin('doc_assignee', 'publish.doc_id', '=', 'doc_assignee.doc_id')
                ->where(function ($query) use ($teamIds, $currentUserId) {
                    $query->whereIn('publish.team_id', $teamIds)
                            ->orWhere('publish.employee_id', $currentUserId)
                            ->orWhere('doc.author_id', $currentUserId)
                            ->orWhere('doc.publisher_id', $currentUserId)
                            ->orWhere('doc_assignee.employee_id', $currentUserId);
                })
                ->where('publish.doc_id', $doc->id)
                ->first();
        if ($hasPermiss) {
            return true;
        }
        return false;
    }
}
