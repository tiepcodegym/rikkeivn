<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Rikkei\Tag\Model\Tag;

class TagController extends Controller 
{
    /**
     * search tag follow field code
     *
     * @param string $fieldCode
     * @return type
     */
    public function searchTagSelect2($fieldCode = null)
    {
        return Tag::searchTagFollowFieldCodeSelect2(Input::get('q'), $fieldCode);
    }
}
