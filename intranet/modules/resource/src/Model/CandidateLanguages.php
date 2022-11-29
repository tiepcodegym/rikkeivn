<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Languages;
use DB;

class CandidateLanguages extends CoreModel
{
    
    protected $table = 'candidate_lang';
    
    const KEY_CACHE = 'candidate_lang';
    
    protected $fillable = ['candidate_id', 'lang_id', 'lang_level_id'];
    
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
     * Save data
     * 
     * @param array $data
     * @param Int $candidateId
     */
    static function saveData($data, $candidateId) {
        $insertData = [];
        if (is_array($data)) {
            foreach ($data as $languageId => $levelId) {
                $insertData[] = [
                    'candidate_id' => $candidateId,
                    'lang_id' => $languageId,
                    'lang_level_id' => $levelId ? $levelId : null,
                ];
            }
            DB::beginTransaction();
            try {
                self::insert($insertData);
                DB::commit();
            } catch (Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }
    }
    
    public static function getLangSelectedLabel($candidateId) {
        $LangTableName = Languages::getTableName();
        return self::join("{$LangTableName}", "{$LangTableName}.id", "=", "candidate_lang.lang_id")
                    ->where('candidate_id', $candidateId)
                    ->select(DB::raw("group_concat(distinct {$LangTableName}.name SEPARATOR ', ') as lang_selected"))
                    ->first();
    }
    
    public static function getListByCandidate($candidateId) {
        return self::where('candidate_id', $candidateId)
                    ->select('*')
                    ->get();
    }
}