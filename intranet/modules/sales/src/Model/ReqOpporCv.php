<?php

namespace Rikkei\Sales\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\View\Permission;

class ReqOpporCv extends CoreModel
{
    protected $table = 'request_oppor_cv_member';
    protected $fillable = ['req_oppor_id', 'note', 'created_by'];

    /**
     * get list cv notes
     * @param type $requestId
     * @return type
     */
    public static function getList($requestId)
    {
        $selfTbl = self::getTableName();
        return self::select(
            $selfTbl.'.id',
            $selfTbl.'.note',
            'emp.name',
            $selfTbl.'.created_by',
            DB::raw('SUBSTRING_INDEX(emp.email, "@", 1) as account'),
            DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(role.role, " - ", team.name)) SEPARATOR ", ") as team_names'),
            $selfTbl.'.created_at',
            $selfTbl.'.updated_at'
        )
            ->join(Employee::getTableName().' as emp', 'emp.id', '=', $selfTbl.'.created_by')
            ->join(TeamMember::getTableName().' as tmb', 'tmb.employee_id', '=', $selfTbl.'.created_by')
            ->join(Team::getTableName().' as team', 'team.id', '=', 'tmb.team_id')
            ->join(Role::getTableName().' as role', 'role.id', '=', 'tmb.role_id')    
            ->where($selfTbl.'.req_oppor_id', $requestId)
            ->whereNull('emp.deleted_at')
            ->groupBy($selfTbl.'.id')
            ->orderBy($selfTbl.'.created_at', 'desc')
            ->paginate(10);
    }

    /**
     * insert or update note cv
     * @param type $cvId
     * @param type $data
     * @return type
     */
    public static function insertOrUpdate($cvId = null, $data = [], $reqOppor = null)
    {
        if ($cvId) {
            $item = self::find($cvId);
            if (!$item) {
                return null;
            }
            $item->update($data);
        } else {
            $currentUser = Permission::getInstance()->getEmployee();
            $data['created_by'] = $currentUser->id;
            $item = self::create($data);
            if ($reqOppor) {
                $sale = $reqOppor->sale;
                if ($sale && $sale->id != $currentUser->id) {
                    $detailLink = route('sales::req.apply.oppor.view', $reqOppor->id);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($sale->email, $sale->name)
                            ->setTemplate('sales::req-oppor.mails.has-note', [
                                'dearName' => $sale->name,
                                'opporName' => $reqOppor->name,
                                'detailLink' => $detailLink,
                                'authorName' => $currentUser->name . ' (' . $currentUser->getNickName() . ')',
                                'comment' => $item->note
                            ])
                            ->setSubject(trans('sales::view.mail_subject_has_cv_note', ['author' => $currentUser->getNickName(), 'name' => $reqOppor->name]))
                            ->setNotify($sale->id, null, $detailLink, ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]);

                    //if has creator and creator not current user or saler
                    $creator = $reqOppor->creator;
                    if ($creator && $creator->id != $currentUser->id && $creator->id != $sale->id) {
                        $emailQueue->addCc($creator->email)
                                ->addCcNotify($creator->id);
                    }
                    $emailQueue->save();
                }
            }
        }
        return $item;
    }
}

