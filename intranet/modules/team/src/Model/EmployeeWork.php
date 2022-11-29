<?php

namespace Rikkei\Team\Model;

use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;

class EmployeeWork extends EmployeeItemRelate
{
    protected $table = 'employee_works';
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $fillable = [
        'employee_id',
        'tax_code',
        'bank_account',
        'bank_name',
        'insurrance_book',
        'insurrance_date',
        'insurrance_ratio',
        'contract_type',
        'insurrance_h_code',
        'insurrance_h_expire',
        'register_examination_place'
    ];

    /**
     * get all type contract
     *
     * @return array
     */
    public static function getAllTypeContract()
    {
        return [
            getOptions::WORKING_UNLIMIT => trans('team::profile.Contract unlimit time'),//1
            getOptions::WORKING_OFFICIAL => trans('team::profile.Contract limit time'),//2
            getOptions::WORKING_PARTTIME => trans('team::profile.Contract seasonal'),//3
            getOptions::WORKING_PROBATION => trans('team::profile.Contract probationary'),//4
            getOptions::WORKING_INTERNSHIP => trans('team::profile.Contract apprenticeship'),//5
            getOptions::WORKING_BORROW => trans('team::profile.Contract borrow'),//6
        ];
    }

    /*
     * get list contact type external
     */
    public static function contractTypeExternal()
    {
        return getOptions::workingTypeExternal();
    }

    public static function contractTypeInternal()
    {
        return getOptions::workingTypeInternal();
    }

    /*
     * update candidate working type before save
     */
    public function save(array $options = array())
    {
        // #4329
//        if ($this->contract_type) {
//            $candidate = Candidate::where('employee_id', $this->employee_id)->first();
//            if ($candidate) {
//                $candidate->working_type = $this->contract_type;
//                $candidate->save();
//            }
//        }
        parent::save($options);
    }
}
