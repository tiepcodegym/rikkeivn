<?php

namespace Rikkei\Test\Models;

use Illuminate\Support\Facades\Config as CoreConfig;
use Illuminate\Support\Facades\Storage;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CoreImageHelper;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Test\View\ViewTest;
use Rikkei\Team\Model\Employee;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\Category;
use Carbon\Carbon;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\Session;
use Rikkei\Test\Models\TestQuestion;
use Rikkei\Core\View\CoreLang;
use Illuminate\Support\Facades\DB;

class Test extends CoreModel
{
    protected $table = 'ntest_tests';
    protected $fillable = ['url_code', 'type', 'name', 'slug', 'time', 'thumbnail',
        'description', 'is_auth', 'random_order', 'random_answer', 'limit_question', 'is_lunar',
        'total_question','type_id', 'time_start', 'time_end', 'set_valid_time', 'created_by',
        'question_cat_ids', 'display_option', 'show_detail_answer', 'valid_view_time', 'set_min_point', 'min_point', 'written_cat', 'total_written_question'];

    public $colsSync = [
        'time',
        'thumbnail',
        'is_auth',
        'random_order',
        'random_answer',
        'limit_question',
        'total_question',
        'type_id',
        'time_start',
        'time_end',
        'set_valid_time',
        'question_cat_ids',
        'display_option',
        'show_detail_answer',
        'valid_view_time',
        'set_min_point',
        'min_point',
    ];

    const TYPE_GMAT = 1;
    const TYPE_SUBJECT = 0;
    
    /**
     * Type test auth
     */
    const IS_NOT_AUTH = 0; // public 
    const IS_AUTH = 1; // private (login to test)

    /**
     * Type set valid time or not
     */ 
    const SET_VALID_TIME = 1;
    const NOT_SET_VALID_TIME = 0;

    /**
     * Type set min point
     */
    const SET_MIN_POINT = 1;
    const NOT_SET_MIN_POINT = 0;
    
    /*
     * thread time view result (minute)
     */
    const VIEW_RESULT_TIME = 30;
    const SET_VIEW_TIME = 1;
    const NOT_SET_VIEW_TIME = 0;

    /**
     * Type set show answer
     */
    const SHOW_RESULT_ONLY = 0;
    const SHOW_WRONG_ANSWER = 1;
    const SHOW_ALL_ANSWER = 2;

    /**
     * path contains thumbnail
     */
    const THUMBNAIL_FOLDER = 'tests/thumbnail/';
    
    /**
     * get collection to show grid data
     * 
     * @return collection model
     */
    public static function getGridData($langCode = null)
    {
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $aryLangCode = array_keys(CoreLang::allLang());
        $pager = Config::getPagerData();
        $testTbl = self::getTableName();
        $typeTbl = Type::getTableName();
        $empTbl = Employee::getTableName();
        $langGroupTbl = LangGroup::getTableName();
        $tblViewTest = 'tests_statistics';
        $currentUser = Permission::getInstance()->getEmployee();
        $scopeRoute = 'test::admin.test.index';

        $collection = self::select(
                'test.id',
                'test.url_code',
                'test.type_id',
                'test.name',
                'test.created_by',
                'test.time',
                'test.is_auth',
                'test.total_question',
                'test.created_at',
                'test.created_by',
                'emp.email',
                'ts.count_result',
                'ts.count_result_pl',
                'ts.count_questions',
                'ts.display_question',
                'lang_group.group_id',
                'lang_group.lang_code'
            )
                ->from($testTbl . ' as test')
                ->leftJoin($tblViewTest . ' as ts', 'ts.id', '=', 'test.id')
                ->leftJoin($typeTbl . ' as type', 'test.type_id', '=', 'type.id')
                ->leftJoin($empTbl . ' as emp', 'test.created_by', '=', 'emp.id')
                ->join($langGroupTbl . ' as lang_group', function ($join) use ($aryLangCode) {
                    $join->on('lang_group.test_id', '=', 'test.id')
                        ->whereIn('lang_group.lang_code', $aryLangCode);
                })
                ->leftJoin($langGroupTbl . ' as gr_lang', function ($join) use ($langCode) {
                    $join->on('gr_lang.test_id', '=', 'test.id')
                        ->where('gr_lang.lang_code', '=', $langCode);
                })
                //if not exists current lang then get default lang
                ->where(function ($query) use ($langCode) {
                    $query->whereNull('gr_lang.group_id')
                        ->orWhere('gr_lang.lang_code', $langCode);
                })
                ->groupBy('lang_group.group_id');

        self::filterGrid($collection, [], null, 'LIKE');
        //permission
        if (Permission::getInstance()->isScopeCompany(null, $scopeRoute)) {
            //get all items
        } elseif (Permission::getInstance()->isScopeTeam(null, $scopeRoute)) {
            $teamIds = $currentUser->getTeamPositons()->lists('team_id')->toArray();
            $employeeIds = TeamMember::whereIn('team_id', $teamIds)
                    ->lists('employee_id')
                    ->toArray();
            $collection->whereIn('emp.id', $employeeIds);
        } elseif (Permission::getInstance()->isScopeSelf(null, $scopeRoute)) {
            $collection->where('emp.id', $currentUser->id);
        } else {
            //no permission
            return CoreView::viewErrorPermission();
        }
        //filter data
        $typeId = Form::getFilterData('excerpt', 'type_id');
        if ($typeId) {
            $collection->whereIn('type_id', Type::allIds($typeId));
        }
        $filterAuth = Form::getFilterData('excerpt', 'is_auth');
        if ($filterAuth !== null && is_numeric($filterAuth)) {
            $collection->where('is_auth', $filterAuth);
        }
        
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * get questions
     * @return type
     */
    public function questions($withOrder = true) {
        $collect = $this->belongsToMany('\Rikkei\Test\Models\Question', 'ntest_test_question', 'test_id', 'question_id')
                ->withPivot('order');
        if ($withOrder) {
            $collect->orderBy('order', 'asc');
        }
        return $collect;
    }
    /**
     * get written questions
     * @return type
     */
    public function writtenQuestions($withOrder = true) {
        $collect = $this->belongsToMany('\Rikkei\Test\Models\WrittenQuestion')
                ->withPivot('order');
        if ($withOrder) {
            $collect->orderBy('order', 'asc');
        }
        return $collect;
    }
    
    /**
     * generate random unique code
     * @return type
     */
    public static function genCode()
    {
        $randCode = str_random(32);
        if (self::where('url_code', $randCode)->first()) {
            $randCode = self::genCode();
        }
        return $randCode;
    }
    
    /**
     * get results relationship
     * @return type
     */
    public function results() {
        return $this->hasMany('\Rikkei\Test\Models\Result', 'test_id', 'id');
    } 
    
    public static function getGMATId()
    {
        $key = 'key_test_gmat_type';
        if (($gmatId = CacheHelper::get($key)) !== null){
            return $gmatId;
        }
        $gmat = Type::where('code', ViewTest::GMAT_CODE)->first();
        if (!$gmat) {
            return 0;
        }
        CacheHelper::put($key, $gmat->id);
        return $gmat->id;
    }
    
    /**
     * get group types option
     * @return type
     */
    public static function groupTypesLabel() {
        $groupTypes = Type::getGroupType();
        if ($groupTypes->isEmpty()) {
            return [];
        }
        $results = [];
        foreach ($groupTypes as $group) {
            $results[$group->id] = [
                'label' => $group->name,
                'target' => '#' . $group->code
            ];
        }
        return $results;
    }
    
    /**
     * get test type
     * @return type
     */
    public function subjectType() {
        return $this->belongsTo('\Rikkei\Test\Models\Type', 'type_id');
    }
    
    /**
     * get label test type
     * @return string
     */
    public function getTypeLabel() {
        $subjectType = $this->subjectType;
        if ($subjectType) {
            return $subjectType->name;
        }
        return null;
    }
    
    /**
     * get employee belongs to
     * @return type
     */
    public function author() 
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'created_by', 'id');
    }
    
    /**
     * select 2 search ajax
     * @param type $name
     * @param type $data
     * @return type
     */
    public static function searchAjax($name, $data = [])
    {
        $arrayDefault = [
            'page' => 1,
            'limit' => 20
        ];
        $config = array_merge($arrayDefault, $data);
        
        $collection = self::select('name as text', 'id')
                ->where('name', 'like', '%'. $name . '%');
        
        self::pagerCollection($collection, $config['limit'], $config['page']);
        
        return [
            'total_count' => $collection->total(),
            'items' => $collection->toArray()['data']
        ];
    }
    
    /**
     * get list test that not done by candidate
     */
    public static function getTestsNotDone(
        $email,
        $testType,
        $candidateId = null,
        $testerType = ViewTest::TESTER_PRIVATE,
        $langCode = null
    )
    {
        $resultTbl = Result::getTableName();
        $testTbl = self::getTableName();
        $langGroupTbl = LangGroup::getTableName();
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $aryInLangs = array_keys(CoreLang::allLang());
        $aryLangStrs = implode(',', array_map(function ($code) {
            return '"'. $code .'"';
        }, $aryInLangs));

        $result = self::from($testTbl . ' as test')
            ->leftJoin($resultTbl . ' as result', function ($join) use ($email, $candidateId, $testerType) {
                $join->on('test.id', '=', 'result.test_id')
                        ->where('result.tester_type', '=', $testerType)
                        ->where('result.created_at', '>=',
                                Carbon::now()->subMinutes(self::VIEW_RESULT_TIME)->toDateTimeString());
                if ($candidateId) {
                    $join->where('result.candidate_id', '=', $candidateId);
                } else {
                    $join->where('result.employee_email', '=', $email);
                }
            })
            //join with current lang and default lang
            ->join($langGroupTbl . ' as group', function ($join) {
                $join->on('group.test_id', '=', 'test.id');
            })
            ->leftJoin($langGroupTbl . ' as gr_lang', function ($join) use ($langCode) {
                $join->on('gr_lang.test_id', '=', 'test.id')
                    ->whereIn('gr_lang.lang_code', [$langCode, CoreLang::DEFAULT_LANG]);
            })
            //if not exists current lang then get default lang
            ->where(function ($query) use ($langCode) {
                $query->orWhere('gr_lang.lang_code', $langCode)
                    ->orWhere('gr_lang.lang_code', CoreLang::DEFAULT_LANG)
                    ->orWhereNull('gr_lang.group_id');
            })
            ->where('test.is_auth', Test::IS_NOT_AUTH)
            //get not in result
            ->whereNull('result.id')
            ->where('test.type_id', $testType)
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('test.set_valid_time', self::SET_VALID_TIME)
                        ->where('test.time_end', '>=', Carbon::now()->format('Y-m-d H:i').':00');
                })->orWhere('test.set_valid_time', self::NOT_SET_VALID_TIME);
            })
            //where not in done type ids
            ->whereNotIn('group.group_id', function ($query) use (
                $resultTbl,
                $testTbl,
                $testerType,
                $candidateId,
                $email,
                $langGroupTbl
            ) {
                $query->select('group.group_id')
                    ->from($resultTbl . ' as result')
                    ->join($testTbl . ' as test', function ($join) {
                        $join->on('result.test_id', '=', 'test.id');
                    })
                    ->join($langGroupTbl . ' as group', 'group.test_id', '=', 'result.test_id')
                    ->where('result.tester_type', $testerType)
                    //where created at lte VIEW RESULT TIME
                    ->where('result.created_at', '>=', Carbon::now()->subMinutes(self::VIEW_RESULT_TIME)->toDateTimeString());

                if ($candidateId) {
                    $query->where('result.candidate_id', $candidateId);
                } else {
                    $query->where('result.employee_email', $email);
                }
            })
            ->select('test.*', 'group.group_id', 'group.lang_code')
            ->groupBy('group.group_id');

        return $result->get();
    }
    
    /*
     * set list question cat ids
     */
    public function setQuestionCatIdsAttribute($value)
    {
        if ($value && is_array($value)) {
            $value = serialize($value);
        }
        if (!$value) {
            $value = null;
        }
        $this->attributes['question_cat_ids'] = $value;
    }
    
    /*
     * get list categories
     */
    public function getQuestionCats($type = null)
    {
        $catIds = $this->question_cat_ids;
        if (!$catIds) {
            return null;
        }
        $catIds = unserialize($catIds);
        $cats = Category::joinLang()
            ->select('id', 'name', 'type_cat')
            ->whereIn('id', $catIds);
        if ($type) {
            $cats = $cats->where('type_cat', $type);
        }

        return $cats->get();
    }
    
    /**
     * collect list categories
     * @param array $arrayCats
     * @param collection $categories
     */
    public static function collectCats(&$arrayCats, $categories)
    {
        if (!$categories->isEmpty()) {
            $arrayCats = array_unique(array_merge($arrayCats, $categories->lists('id')->toArray()));
        }
    }
    
    /**
     * set display option value
     * @param type $value
     */
    public function setDisplayOptionAttribute($value)
    {
        if ($value && is_array($value)) {
            foreach ($value as $key => $option) {
                if (!isset($option['value']) || !$option['value']) {
                    unset($value[$key]);
                }
            }
            $value = serialize($value);
        }
        $this->attributes['display_option'] = $value ? $value : null;
    }
    
    /**
     * get dispaly option
     * @return type
     */
    public function getDisplayOption()
    {
        if (!$this->display_option) {
            return null;
        }
        return unserialize($this->display_option);
    }

    /**
     * check test has someone testing
     * @return boolean
     */
    public function checkTesting()
    {
        $hasTesting = TestTemp::where('test_id', $this->id)
                ->get();
        if ($hasTesting->isEmpty()) {
            return false;
        }
        return $hasTesting;
    }

    /**
     * upload thumbnail for test
     * @param $uploads
     * @param $test
     * @throws \Exception
     * @return null|array
     */
    public static function uploadThumbnail($uploads, $test = null)
    {
        if (empty($uploads)) {
            return null;
        }
        $fileName = CoreView::uploadFile(
            $uploads,
            CoreConfig::get('general.upload_storage_public_folder') . '/' . self::THUMBNAIL_FOLDER,
            CoreConfig::get('services.file.image_allow'),
            CoreConfig::get('services.file.image_max')
        );
        $newThumbnail = CoreConfig::get('general.upload_folder') . '/' . self::THUMBNAIL_FOLDER . $fileName;

        return [
            'new_thumbnail' => $newThumbnail,
            'old_thumbnail' => $test ? $test->thumbnail : null,
        ];
    }

    /**
     * delete thumbnail in storage
     * @param $path
     */
    public static function deleteThumbnail($path)
    {
        if (empty($path)) {
            return;
        }

        $thumbnailInfo = CoreImageHelper::getInstance()->splitPath($path);
        if (Storage::disk('public')->exists(self::THUMBNAIL_FOLDER . '/' . $thumbnailInfo['1'] . $thumbnailInfo['2'])) {
            Storage::disk('public')->delete(self::THUMBNAIL_FOLDER . '/' . $thumbnailInfo['1'] . $thumbnailInfo['2']);
        }
    }

    public function testAssignees()
    {
        return $this->hasMany(Assignee::class);
    }

    public function langGroup()
    {
        return $this->hasOne('\Rikkei\Test\Models\LangGroup', 'test_id');
    }

    public function getLangCode()
    {
        $langGroup = $this->langGroup;
        if (!$langGroup) {
            return null;
        }
        return $langGroup->lang_code;
    }

    /**
     * find test item by lang
     *
     * @param int $testId test id
     * @param string $langCode vi, en, jp, ...
     * @return object
     */
    public static function findItemByLang($testId, $langCode, $returnDefault = false)
    {
        $testTbl = self::getTableName();
        $groupTbl = LangGroup::getTableName();
        $aryLangCodes = [$langCode];
        if ($returnDefault) {
            $aryLangCodes = array_keys(CoreLang::allLang());
        }
        $results = self::select($testTbl . '.*', 'group.lang_code', 'group.group_id')
            ->join($groupTbl . ' as group', 'group.test_id', '=', $testTbl . '.id')
            ->whereIn('group.lang_code', $aryLangCodes)
            ->whereIn('group.group_id', function ($query) use ($testId, $groupTbl, $testTbl) {
                $query->select('group_id')
                    ->from($groupTbl);
                if (is_numeric($testId)) {
                    $query->where('test_id', $testId);
                } else {
                    $query->whereIn('test_id', function ($subQuery) use ($testId, $testTbl) {
                        $subQuery->select('id')
                            ->from($testTbl)
                            ->where('url_code', $testId);
                    });
                }
            });
        if ($returnDefault) {
            $results = $results->get()->groupBy('lang_code');
            if ($results->isEmpty()) {
                return null;
            }
            if (isset($results[$langCode])) {
                return $results[$langCode]->first();
            }
            if (isset($results[CoreLang::DEFAULT_LANG])) {
                return $results[CoreLang::DEFAULT_LANG]->first();
            }
            return $results->first()->first();
        }
        return $results->first();
    }

    /**
     * collect tests has same group lang
     *
     * @param int $groupId
     * @param array $groupTestIds
     * @param int $exceptTestId
     * @param bool $returnBulder
     * @return mixed Builder|Object
     */
    public static function collectTestsSameGroup(
        $groupId,
        $groupTestIds = [],
        $exceptTestId = null,
        $returnBulder = true
    )
    {
        if (count($groupTestIds) < 1) {
            $collectTests = self::whereIn('id', function ($query) use ($groupId) {
                    $query->select('test_id')
                        ->from(LangGroup::getTableName())
                        ->where('group_id', $groupId);
                });
        } else {
            $collectTests = self::whereIn('id', $groupTestIds);
        }
        if ($exceptTestId) {
            $collectTests->where('id', '!=', $exceptTestId);
        }
        if ($returnBulder) {
            return $collectTests;
        }
        return $collectTests->get();
    }

    /**
     * sync all options of tests by group id 
     *
     * @param int $groupId
     * @param array $groupTestIds array test ids
     */
    public function syncOptionByGroupId($groupId, $groupTestIds = [])
    {
        $colsSyns = $this->colsSync;
        $collectTests = self::collectTestsSameGroup($groupId, $groupTestIds, $this->id, true);

        $dataUpdate = [];
        foreach ($colsSyns as $col) {
            $dataUpdate[$col] = $this->{$col};
        }
        $collectTests->update($dataUpdate);
    }

    /**
     * update question order in test lang group
     *
     * @param array $newQuesOrder [order => question id]
     * @param array $changeOrder [new order => old order]
     * @param int $groupId group test lang id
     * @param array $groupTestIds test ids same group
     * @return void
     */
    public function syncLangQuestionsOrder($newQuesOrder, $changeOrder, $groupId, $groupTestIds = [])
    {
        if (count($groupTestIds) < 1) {
            $testIds = LangGroup::where('group_id', $groupId)
                ->where('test_id', '!=', $this->id)
                ->pluck('test_id', 'lang_code')
                ->toArray();
        } else {
            $testIds = array_filter($groupTestIds, function ($id) {
                return $id && $id != $this->id;
            });
        }
        if (count($testIds) <  1) {
            return;
        }
        $questionList = TestQuestion::listByTestIds($testIds);
        if ($questionList->isEmpty()) {
            return;
        }
        $questionList = $questionList->groupBy('test_id');
        $oldQuesOrder = $this->questions()->pluck('id', 'order')->toArray();
        $syncQuesIds = [];
        //delete old test question
         TestQuestion::whereIn('test_id', $testIds)->delete();
        foreach ($questionList as $testId => $questions) {
            $aryQuesIds = $questions->pluck('question_id', 'order')->toArray();
            //$changeOrder: [new order => old order]
            foreach ($newQuesOrder as $newOrder => $quesId) {
                if (!isset($changeOrder[$newOrder])) {
                    continue;
                }
                $oldOrder = $changeOrder[$newOrder];
                if (!isset($aryQuesIds[$oldOrder])) {
                    continue;
                }
                $syncQuesId = $aryQuesIds[$oldOrder];
                $syncQuesIds[] = [
                    'test_id' => $testId,
                    'question_id' => $syncQuesId,
                    'order' => $newOrder,
                ];
            }
        }
        if (count($syncQuesIds) > 0) {
            TestQuestion::insert($syncQuesIds);
        }
    }
}
