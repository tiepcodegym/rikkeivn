<?php
/**
 * (model) người được ứng cử nếu đã confirm được import từ Nominee.
 */

namespace Rikkei\Vote\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Rikkei\Vote\Model\VoteResult;
use Rikkei\Vote\View\VoteConst;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;

class VoteNominee extends CoreModel {
    
    protected $table = 'vote_nominees';
    protected $dates = ['deleted_at'];
    protected $fillable = ['vote_id', 'nominee_id', 'description', 'key', 'confirm', 'created_by'];


    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    /**
     * get by vote id
     * @param type $voteId
     * @return type
     */
    public static function getByVoteId ($voteId, $isAjax = true) {
        if ($isAjax) {
            $pager = Config::getPagerDataQuery();
        } else {
            $pager = Config::getPagerData(null, ['limit' => 50]);
        }
        
        $voteNomineeTbl = self::getTableName();
        $employeeTbl = Employee::getTableName();
        $userTbl = User::getTableName();
        $voteResultTbl = VoteResult::getTableName();
        
        $collection  = self::select($voteNomineeTbl.'.vote_id', $voteNomineeTbl.'.id as vote_nominee_id', $voteNomineeTbl.'.nominee_id', 'user.avatar_url', 'emp.name', 'emp.email', $voteNomineeTbl.'.description', 
                                    DB::raw('COUNT(DISTINCT(vs.voter_id)) as count_vote'))
                ->join($employeeTbl.' as emp', $voteNomineeTbl.'.nominee_id', '=', 'emp.id')
                ->leftJoin($userTbl.' as user', 'emp.id', '=', 'user.employee_id')
                ->leftJoin($voteResultTbl.' as vs', $voteNomineeTbl.'.id', '=', 'vs.vote_nominee_id')
                ->whereNull('emp.deleted_at')
                ->whereNull('emp.leave_date')
                ->where($voteNomineeTbl.'.vote_id', $voteId)
                ->whereNotNull($voteNomineeTbl.'.confirm')
                ->where($voteNomineeTbl.'.confirm', VoteConst::CONFIRM_YES);
        
        if (!$isAjax) {
            $collection->leftJoin($voteResultTbl.' as vrs', function ($join) use ($voteNomineeTbl) {
                $join->on($voteNomineeTbl.'.id', '=', 'vrs.vote_nominee_id')
                        ->where('vrs.voter_id', '=', auth()->id());
            });
            $collection->addSelect('vrs.voter_id as had_voted')
                    ->orderBy('had_voted', 'desc');
        }
        $collection->groupBy($voteNomineeTbl.'.nominee_id')
                ->orderBy('count_vote', 'desc')
                ->orderBy($voteNomineeTbl.'.updated_at', 'desc');
        
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        
        return $collection;
    }
    
    /**
     * get employee list not in vote nominees
     * @param type $data
     * @return type
     */
    public static function getEmployeesExcerptNominees($data) {
        $q = isset($data['q']) ? $data['q'] : null;
        $voteId = isset($data['vote_id']) ? $data['vote_id'] : null;
        $arrayDefault = [
            'page' => 1,
            'limit' => 20
        ];
        $config = array_merge($arrayDefault, $data);
        $employeeTbl = Employee::getTableName();
        
        //get employee ids in vote nominees
        $employees = VoteNominee::select('nominee_id')
                ->where('confirm', '!=', VoteConst::CONFIRM_YES);
        if ($voteId) {
            $employees->where('vote_id', $voteId);
        }
        $employeeIds = $employees->lists('nominee_id')->toArray();
        
        $collection = Employee::select($employeeTbl.'.id', $employeeTbl.'.email as text', $employeeTbl.'.name')
                ->where($employeeTbl.'.email', 'LIKE', '%' . $q . '%')
                ->whereNull($employeeTbl.'.leave_date')
                ->whereNotIn($employeeTbl.'.id', $employeeIds)
                ->groupBy($employeeTbl.'.id')
                ->orderBy($employeeTbl.'.email', 'asc');
        
        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['items'] = $collection->toArray()['data'];
        
        return $result;
    }
    
    /**
     * generate random unique key
     * @param type $length
     * @return type
     */
    public static function generateKey ($length = 50) {
        $key = str_random($length);
        $item = self::where('key', $key)->first();
        if ($item) {
            $key = self::generateKey($length);
        }
        return $key;
    }
    
    /**
     * insert from input data
     * @param type $data
     * @return type
     */
    public static function insertData ($data, $isSelf = false) {
        $employeeId = $data['nominee_employee_id'];
        $voteId = $data['vote_id'];
        $employee = Employee::find($employeeId, ['id', 'email']);
        if (!$employee) {
            abort(404);
        }

        $dataNew = [
            'vote_id' => $voteId,
            'nominee_id' => $employee->id,
            'description' => isset($data['nominee_description']) ? $data['nominee_description'] : null,
            'created_by' => auth()->id(),
            'key' => isset($data['key']) ? $data['key'] : null,
            'confirm' => isset($data['confirm']) ? $data['confirm'] : null
        ];
        if ($isSelf) {
            $dataNew['confirm'] = VoteConst::CONFIRM_YES;
            $dataNew['key'] = null;
        }
        
        $voteNominee = self::withTrashed()
                ->where('vote_id', $voteId)
                ->where('nominee_id', $employee->id)
                ->first();
        
        $isUpdate = false;
        if ($voteNominee) {
            if (!isset($data['update'])) {
                $dataFill = array_only($dataNew, $voteNominee->getFillable());
                $voteNominee->update($dataFill);
            }
            if ($voteNominee->trashed()) {
                $voteNominee->restore();
            } else {
                $isUpdate = true;
            }
        } else {
            $voteNominee = self::create($dataNew);
        }
        $voteNominee->is_update = $isUpdate;
        
        return $voteNominee;
    }
    
    /**
     * check exists
     */
    public static function checkExists ($vote_id, $nominee_id) {
        $item = self::where('vote_id', $vote_id)
                ->where('nominee_id', $nominee_id)
                ->first();
        if ($item) {
            return true;
        }
        return false;
    }
    
}

