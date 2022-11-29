<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use DB;

class CandidateContactHistory extends CoreModel
{
    
    protected $table = 'candidate_contact_history';
    
    const KEY_CACHE = 'candidate_contact_history';
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    public $timestamps = false;
    
    /**
     * get list history contact by ID candidate
     * 
     * @return objects
     */
    public static function getListHistoryByIdCandidate($id) {
        return self::orderBy('id', 'asc')
            ->where('candidate_id',$id)->select('status','date_contact','id','reason')->get();
    } 

    /**
     * save history contact when submit result tab contact
    */
    public static function saveHistoryContact($data) { 
        DB::beginTransaction();
        try {
            if($data['history_contact_id'] == null) {
                $historyContact = new CandidateContactHistory();
            } else {
                $historyContact = self::find($data['history_contact_id']);
            }
            $historyContact->candidate_id = $data['candidate_id'];
            $historyContact->status = $data['contact_result'];
            $historyContact->date_contact = $data['date_contact'];
            $historyContact->reason = $data['reason'];
            $historyContact->save();
            DB::commit();
        } catch(Exception $ex) {
            Log::info($ex); 
            DB::rollback();
        }
      
    }

    /**
     * get history contact by Id
     * 
     * @return objects
     */
    public static function getHistoryById($id) {
        return self::where('id',$id)->select('status','date_contact','id','reason')->first();
    }    
}