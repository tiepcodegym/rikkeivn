<?php

namespace Rikkei\SlideShow\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SlideBirthday extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'slide_birthdays';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slide_id', 'content', 'avatar'];
    

    /**
     * get slide birthday
     * 
     * @param model $slide
     * @return collection
     */
    public static function getSlideBirthday($slide)
    {
        return self::select('id', 'slide_id','content', 'avatar')
            ->where('slide_id', $slide->id)
            ->get();
    }
    
    /**
     * delete slide birthday
     * 
     * @param model $slide
     * @return type
     * @throws Exception
     */
    public static function deleteSlideBirthday($slide)
    {
        try {
            return self::where('slide_id', $slide->id)->delete();
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }
}