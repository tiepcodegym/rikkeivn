<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;

class StorageController extends Controller {
    
    /**
     * export tag data
     * @return array
     */
    public function exportTagData() {
        $version = Input::get('version');
        
        $storageTagVer = CoreConfigData::getItem(TagConst::KEY_TAG_VER);
        if (!$version || $storageTagVer->value != $version) {
            if (!$storageTagVer->value) {
                $storageTagVer->value = $version ? $version : 1;
                $storageTagVer->save();
            }
            return [
                'tags' => Tag::select('id', 'value', 'field_id')
                                ->get(),
                'fields' => Field::select('id', 'name', 'color')
                                ->get(),
                'version' => $storageTagVer->value
            ];
        }
        return [];
    }
    
}

