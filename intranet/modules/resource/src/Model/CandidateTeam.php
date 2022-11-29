<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class CandidateTeam extends CoreModel
{
    
    protected $table = 'candidate_team';
    public $timestamps = false;
    
    const KEY_CACHE = 'candidate_team';
    
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
    
}