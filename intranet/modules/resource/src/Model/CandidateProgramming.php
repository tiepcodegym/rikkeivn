<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;

class CandidateProgramming extends CoreModel
{
    protected $table = 'candidate_programming';
    
    const KEY_CACHE = 'candidate_programming';

    /**
     * store this object
     * @var object
     */
    protected static $instance;

    /**
     * get list
     *
     * @return objects
     */
    public function getList()
    {
        if ($item = CacheHelper::get(self::KEY_CACHE)) {
            return $item;
        }
        return self::orderBy('name', 'asc')->select('*')->get();
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public static function getListByCandidate($candidateId) {
        return self::where('candidate_id', $candidateId)
                    ->select('*')
                    ->get();
    }
}