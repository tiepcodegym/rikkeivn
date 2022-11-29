<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Team\Model\Skill;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\Model\School;
use Rikkei\Team\Model\Certificate;

class AjaxController extends Controller
{
    /**
     * get skill data
     */
    public function skillSearch()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return [
            'data' => Skill::searchSkillAutocomplete(Input::get('term'), Input::get('type'))
        ];
    }

    /**
     * get skill data
     */
    public function eduSearch()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return [
            'data' => School::searchSchoolAutocomplete(Input::get('term'))
        ];
    }

    /**
     * get skill data
     */
    public function cerSearch()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return [
            'data' => Certificate::searchSchoolAutocomplete(Input::get('term'))
        ];
    }
}
