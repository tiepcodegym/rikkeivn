<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class CandidateRequest extends CoreModel
{
    
    protected $table = 'candidate_request';
    public $timestamps = false;
    
    const KEY_CACHE = 'candidate_request';
    
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

    public static function getRequest($id)
    {
        return self::join('requests', 'requests.id', '=', 'candidate_request.request_id')
            ->join('request_team', 'request_team.request_id', '=', 'requests.id')
            ->select(
                'requests.id',
                'requests.title',
                'request_team.team_id'
            )
            ->where('candidate_request.candidate_id', $id)
            ->groupBy('requests.id')
            ->get();
    }
}