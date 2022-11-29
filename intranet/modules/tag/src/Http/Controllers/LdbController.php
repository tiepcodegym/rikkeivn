<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Tag\View\TagConst;
use Rikkei\Tag\Model\ViewProjTag;
/**
 * local store db
 */
class LdbController extends Controller 
{
    /**
     * get version local db
     */
    public function version()
    {
        return CoreConfigData::getValueDb(TagConst::KEY_CONFIT_LDB_VERSION);
    }
    
    /**
     * get all data project tag
     */
    public function getAllProjTag()
    {
        return response()->json(ViewProjTag::getAllData());
    }
}
