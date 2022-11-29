<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;

class Candidate extends CoreModel
{
    protected $table = 'candidate_informations';
    protected $fillable = ['full_name', 'email', 'birth', 'identify', 'home_town', 'phone_number', 
        'position', 'salary', 'start_time', 'had_worked', 'hear_recruitment', 'relatives', 'recruiter'];

    
    /**
     * get list fields
     * @return array
     */
    public static function getFields() {
        return [
            [
                'key' => 'full_name',
                'label' => trans('test::test.Full name'),
                'required' => true
            ],
            [
                'key' => 'email',
                'label' => trans('test::test.email'),
                'required' => true
            ],
            [
                'key' => 'birth',
                'label' => trans('test::test.Date of birth'),
                'required' => true
            ],
            [
                'key' => 'identify',
                'label' => trans('test::test.Identify'),
                'required' => true
            ],
            [
                'key' => 'home_town',
                'label' => trans('test::test.Home town'),
                'required' => true
            ],
            [
                'key' => 'phone_number',
                'label' => trans('test::test.Phone number'),
                'required' => true
            ],
            [
                'key' => 'position',
                'label' => trans('test::test.Position recruitment'),
                'required' => true
            ],
            [
                'key' => 'salary',
                'label' => trans('test::test.Desired salary'),
                'required' => true
            ],
            [
                'key' => 'start_time',
                'label' => trans('test::test.If recruited, when can you start the job'),
                'required' => true
            ],
            [
                'key' => 'had_worked',
                'label' => trans('test::test.Have you worked at our company'),
                'required' => true
            ],
            [
                'key' => 'hear_recruitment',
                'label' => trans('test::test.Where did you hear about our recruitment'),
                'required' => true
            ],
            [
                'key' => 'relatives',
                'label' => trans('test::test.Your name or your relatives are working for our company'),
                'required' => false
            ],
            [
                'key' => 'recruiter',
                'label' => trans('test::test.hr_account'),
                'required' => false
            ]
        ];
    }

    /**
     * get all data
     * @return collection
     */
    public static function getGridData() {
        $pager = Config::getPagerData();
        $collection = self::select('*');
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}

