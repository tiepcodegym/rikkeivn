<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use DB;

class CandidatePosition extends CoreModel
{
    
    protected $table = 'candidate_pos';
    public $timestamps = false;
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    /**
     * Delete records by candidate
     * 
     * @param int $candidateId
     */
    public static function deleteByCandidate($candidateId) {
        DB::beginTransaction();
        try {
            $deletedRows = self::where('candidate_id', $candidateId)->delete();
            DB::commit();
            return $deletedRows;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * Insert postions by candidate
     * 
     * @param int $candidateId
     * @param array $positions
     */
    public static function insertPostions($candidateId, $positions) {
        if (is_array($positions) && count($positions)) {
            $data = [];
            foreach ($positions as $pos) {
                $data[] = ['candidate_id' => $candidateId, 'position_apply' => $pos];
            }
            DB::beginTransaction();
            try {
                self::insert($data);
                DB::commit();
            } catch (Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }
    }
    
    /**
     * get Positions by candidate
     * 
     * @param int $candidateId
     * @return CandidatePosition collection
     */
    public static function getPositions($candidateId) {
        return self::where('candidate_id', $candidateId)
                ->select('*')
                ->get();
    }
    
    /**
     * get Position ids by candidate
     * 
     * @param int $candidateId
     * @return array
     */
    public static function getPositionIds($candidateId) {
        $positions = self::where('candidate_id', $candidateId)
                        ->select(['position_apply'])
                        ->get();
        $result = [];
        foreach ($positions as $pos) {
            $result[] = $pos->position_apply;
        }
        return $result;
    }
    
}