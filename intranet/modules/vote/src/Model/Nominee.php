<?php
/**
 * (model) Người được ứng cử, đề cử chưa xác nhận.
 */

namespace Rikkei\Vote\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\User;
use Rikkei\Vote\Model\VoteNominee;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;

class Nominee extends CoreModel {
    
    /**
     * define
     * @var type 
     */
    protected $table = 'vote_nominations';
    protected $fillable = ['vote_id', 'nominee_id', 'nominator_id', 'reason'];
    
    /**
     * get list nominee by vote id
     * @param type $voteId
     * @return type
     */
    public static function getNomineesByVoteId ($voteId) {
        $pager = Config::getPagerDataQuery();
        $nominateTbl = self::getTableName();
        $voteNomineeTbl = VoteNominee::getTableName();
        $employeeTbl = Employee::getTableName();
        $userTbl = User::getTableName();
        
        $collection = self::select($nominateTbl.'.vote_id', $nominateTbl.'.nominee_id', $nominateTbl.'.nominator_id', $nominateTbl.'.reason', 
                DB::raw('COUNT(DISTINCT(IFNULL('. $nominateTbl .'.nominator_id, 0))) as count_nominate'),
                'emp.name', 'emp.email', 'user.avatar_url', 'nme.confirm', 'nme.id as vote_nominee_id')
                ->join($employeeTbl.' as emp', $nominateTbl.'.nominee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->whereNull('emp.leave_date')
                ->leftJoin($userTbl.' as user', 'emp.id', '=', 'user.employee_id')
                ->leftJoin($voteNomineeTbl.' as nme', function ($join) use ($nominateTbl, $voteId) {
                    $join->on($nominateTbl.'.nominee_id', '=', 'nme.nominee_id')
                            ->where('nme.vote_id', '=', $voteId)
                            ->whereNull('nme.deleted_at');
                })
                ->where($nominateTbl.'.vote_id', $voteId)
                ->groupBy($nominateTbl.'.nominee_id')
                ->orderBy('count_nominate', 'desc')
                ->orderBy($nominateTbl.'.created_at', 'desc');
        
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * get list nominators by vote id and nominee id
     * @param type $voteId
     * @param type $nomineeId
     * @return type
     */
    public static function getNominatorsByVoteId ($voteId, $nomineeId) {
        $pager = Config::getPagerDataQuery();
        $nomineeTbl = self::getTableName();
        $employeeTbl = Employee::getTableName();
        
        $collection = self::select('emp.name', 'emp.email', $nomineeTbl.'.created_at', $nomineeTbl.'.reason',
                                    DB::raw('CASE WHEN emp.id IS NULL THEN 1 ELSE 2 END AS type'))
                ->leftJoin($employeeTbl.' as emp', $nomineeTbl.'.nominator_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->where($nomineeTbl.'.vote_id', $voteId)
                ->where($nomineeTbl.'.nominee_id', $nomineeId)
                ->orderBy('type', 'asc')
                ->orderBy('created_at', 'desc');
        
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * get employees excerpt in nominations
     * @param type $data
     * @return type
     */
    public static function getEmployeesExcerpt ($data) {
        $q = isset($data['q']) ? $data['q'] : null;
        $voteId = isset($data['vote_id']) ? $data['vote_id'] : null;
        $excerptCurrent = isset($data['excerpt_current']) ? $data['excerpt_current'] : null;
        $hasNominator = isset($data['has_nominator']) ? $data['has_nominator'] : null;
        $nominatorId = auth()->id();
        
        //get employee ids in vote nominations
        $employees = self::select('nominee_id');
        if ($voteId) {
            $employees->where('vote_id', $voteId);
        }
        if ($hasNominator) {
            $employees->where('nominator_id', $nominatorId);
        }
        $employeeIds = $employees->lists('nominee_id')->toArray();
        if ($excerptCurrent) {
            array_push($employeeIds, $nominatorId);
        }
        $result = Employee::searchAjax($q, [
            'typeExclude' => Employee::EXCLUDE_EMPLOYEE,
            'excerpt_ids' => $employeeIds,
            'page' => isset($data['page']) ? $data['page'] : 1
        ]);
        return $result;
    }
    
    /**
     * get remaining number nominee of current user
     * @param type $vote
     * @return number/null
     */
    public static function getRemainNominee ($vote) {
        if (!is_object($vote)) {
            $vote = Vote::find($vote);
        }
        $nomineeMax = $vote->nominee_max;
        if (!$nomineeMax) {
            return null;
        }
        $nominated = self::where('vote_id', $vote->id)
                        ->where('nominator_id', auth()->id())
                        ->get()->count();
        return $nomineeMax - $nominated;
    }
    
    /**
     * check current user nominated
     * @param type $voteId
     * @param type $nomineeId
     * @return type
     */
    public static function checkExists ($voteId, $nomineeId, $isSelf = false) {
        if (!$isSelf) {
            return self::where('vote_id', $voteId)
                    ->where('nominee_id', $nomineeId)
                    ->where('nominator_id', auth()->id())
                    ->first();
        }
        return self::where('vote_id', $voteId)
                ->where('nominee_id', $nomineeId)
                ->whereNull('nominator_id')
                ->first();
    }
    
}

