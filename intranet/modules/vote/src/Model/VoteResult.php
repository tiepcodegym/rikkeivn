<?php

namespace Rikkei\Vote\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Vote\Model\VoteNominee;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;

class VoteResult extends CoreModel {
    
    protected $table = 'vote_results';
//    protected $primaryKey = ['vote_nominee_id', 'voter_id'];
//    public $incrementing = false;
    
    /**
     * get voter (employee) by vote_nominee_id (vote_nominees)
     * @param type $voteNomineeId
     * @return type
     */
    public static function getVotersByVoteNomineeId ($voteNomineeId) {
        $pager = Config::getPagerDataQuery();
        $employeeTbl = Employee::getTableName();
        $voteResultTbl = self::getTableName();
        $voteNomineeTbl = VoteNominee::getTableName();
        
        $collection = self::select('emp.name', 'emp.email', $voteResultTbl.'.created_at', 
                                    DB::raw('CASE WHEN nme.nominee_id = '. $voteResultTbl .'.voter_id THEN 1 ELSE 2 END AS type'))
                ->join($employeeTbl.' as emp', $voteResultTbl.'.voter_id', '=', 'emp.id')
                ->join($voteNomineeTbl.' as nme', $voteResultTbl.'.vote_nominee_id', '=', 'nme.id')
                ->where($voteResultTbl.'.vote_nominee_id', $voteNomineeId)
                ->orderBy('type', 'asc')
                ->orderBy($voteResultTbl.'.created_at', 'desc');
        
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * get remain vote of current user
     * @param type $voteNominee
     * @return type
     */
    public static function getRemainVote ($vote) {
        if (!is_object($vote)) {
            $vote = Vote::find($vote);
        }

        $voteMax = $vote->vote_max;
        if (!$voteMax) {
            return null;
        }
        $voterId = auth()->id();
        $voteNomineeTbl = VoteNominee::getTableName();
        $voteResultTbl = self::getTableName();
        $item = self::select(DB::raw('COUNT(DISTINCT(nme.id)) as count_vote'))
                ->join($voteNomineeTbl.' as nme', $voteResultTbl.'.vote_nominee_id', '=', 'nme.id')
                ->where('nme.vote_id', $vote->id)
                ->whereNull('nme.deleted_at')
                ->where($voteResultTbl.'.voter_id', $voterId)
                ->groupBy($voteResultTbl.'.voter_id')
                ->first();
        if (!$item) {
            return $voteMax;
        }
        return $voteMax - $item->count_vote;
    }
    
    public static function checkVoted ($voteNomineeId, $voterId = null) {
        if (!$voterId) {
            $voterId = auth()->id();
        }
        return self::where('vote_nominee_id', $voteNomineeId)
                ->where('voter_id', $voterId)
                ->first();
    }
    
    /**
     * add or remove vote
     * @param type $voteNomineeId
     * @param type $voterId
     * @return boolean
     */
    public static function addOrRemoveVote ($voteNomineeId, $voterId = null) {
        if (!$voterId) {
            $voterId = auth()->id();
        }
        $item = self::checkVoted($voteNomineeId);
        if (!$item) {
            $item = new VoteResult();
            $item->vote_nominee_id = $voteNomineeId;
            $item->voter_id = $voterId;
            $item->save();
            return true;
        } else {
            self::where('vote_nominee_id', $voteNomineeId)
                ->where('voter_id', $voterId)
                ->delete();
            return false;
        }
    }
    
}

