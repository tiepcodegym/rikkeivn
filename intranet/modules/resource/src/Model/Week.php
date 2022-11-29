<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class Week extends CoreModel
{
    protected $table = 'candidate_weeks';
    protected $primaryKey = 'week';
    protected $fillable = ['week', 'number_cvs', 'tests', 'tests_pass', 'gmats_8', 'interviews', 'interviews_pass', 'offers', 'offers_pass', 'workings'];
    public $timestamps = false;
    public $incrementing = false;

    /*
     * get attribute and convert to array
     */
    public function getJsonAttribute($field, $paramWiths = [])
    {
        $collect = json_decode($this->{$field});
        $collect = collect($collect);
        $this->filterParams($collect, $paramWiths);
        $collect = collect(array_values($collect->toArray()));
        return $collect;
    }

    /*
     * filter array collection
     */
    public function filterParams(&$collect, $paramWiths = [])
    {
        if (isset($paramWiths['recruiter'])) {
            $collect = $collect->where('recruiter', $paramWiths['recruiter']); //filter recruiter
        }
        if (isset($paramWiths['scope_company'])) { //filter permission
            //all
        } elseif (isset($paramWiths['scope_team'])) {
            $collect = $collect->whereIn('recruiter', $paramWiths['employee_emails']);
        } elseif (isset($paramWiths['scope_self'])) {
            $collect = $collect->where('recruiter', $paramWiths['current_email']);
        } else {
            //not have permission
        }
    }
}
