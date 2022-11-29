<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\DB;
use Rikkei\Test\Models\Test;
use Rikkei\Test\View\ViewTest;
use Carbon\Carbon;
use Rikkei\Test\Models\TestResult;
use Rikkei\Test\Models\TestTemp;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Test\Models\LangGroup;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\CoreLang;

class Result extends CoreModel
{
    protected $table = 'ntest_results';

    protected $fillable = [
        'employee_id',
        'candidate_id',
        'employee_email',
        'employee_name',
        'phone',
        'tester_type',
        'test_id',
        'total_answers',
        'total_corrects',
        'leaved_at',
        'question_index',
        'total_question',
        'random_labels',
        'begin_at',
        'created_at',
        'updated_at',
        'written_index',
    ];

    protected $dates = ['leaved_at'];
    protected $appends = ['tester_type'];
    public $colsSync = [
        'total_question',
        'employee_name',
        'employee_email',
        'phone',
        'candidate_id',
        'tester_type',
        'begin_at',
        'leaved_at',
        'created_at',
        'updated_at',
        'total_corrects',
        'total_answers',
    ];

    /**
     * get test result detail
     * @return type
     */
    public function details() {
        return $this->hasMany('\Rikkei\Test\Models\TestResult', 'test_result_id');
    }

    /**
     * check do test by email and test id
     * @param type $email
     * @param type $test_id
     * @return boolean
     */
    public static function checkDoTest(
        $email,
        $testId,
        $candidateId = null,
        $testerType = ViewTest::TESTER_PRIVATE
    )
    {
        $test = $testId instanceof Test ? $testId : Test::find($testId);
        if (!$test) {
            return false;
        }

        $testId = $test->id;
        //if not valid view time then return false
        $validViewTime = $test->valid_view_time;
        if (!$validViewTime) {
            return false;
        }

        $groupTbl = LangGroup::getTableName();
        $check = self::select('result.*')
            ->from(self::getTableName() . ' as result')
            ->join($groupTbl . ' as group', 'group.test_id', '=', 'result.test_id')
            ->where('result.employee_email', $email)
            ->whereIn('group.group_id', function ($query) use ($testId, $groupTbl) {
                $query->select('group_id')
                    ->from($groupTbl)
                    ->where('test_id', $testId);
            })
            ->where('result.tester_type', $testerType)
            ->orderBy('updated_at', 'desc');

        if ($candidateId) {
            $check->where('candidate_id', $candidateId);
        }

        $check = $check->first();
        if ($check) {
            $timeUpdate = $check->updated_at;
            $timeNow = Carbon::now();
            if ($timeNow->gt($timeUpdate->addMinutes(Test::VIEW_RESULT_TIME))) {
                return false;
            }
            return $check->id;
        }
        return false;
    }

    /**
     * get test result by email and test id
     * @param string $email
     * @param int|null $isAuth
     * @return type
     */
    public static function getByEmailType(
        $email,
        $isAuth = Test::IS_NOT_AUTH,
        $candidateId = null,
        $testerType = ViewTest::TESTER_PRIVATE
    )
    {
        $tblTest = Test::getTableName();
        $tblType = Type::getTableName();
        $tblResult = self::getTableName();
        $tblTestQues = 'ntest_test_question';

        $result = self::from('ntest_results as result')
                ->join(DB::raw('('
                        . 'SELECT test1.*, type1.name AS type_name, COUNT(test_q.question_id) AS count_question '
                        . 'FROM ' . $tblTest . ' AS test1 '
                        . 'LEFT JOIN ' . $tblType . ' AS type1 '
                        . 'ON test1.type_id = type1.id '
                        . 'LEFT JOIN ' . $tblTestQues . ' AS test_q '
                        . 'ON test1.id = test_q.test_id '
                        . 'GROUP BY test1.id'
                        . ') AS test_type'), 'result.test_id', '=', 'test_type.id')
                ->where('test_type.is_auth', '?');

        $arrayBindings = [$isAuth];
        if ($candidateId) {
            $result->where('result.candidate_id', '?');
            $queryMaxDate = 'result2.candidate_id = ?';
            $arrayBindings[] = $candidateId;
            $arrayBindings[] = $candidateId;
        } else {
            $result->where('result.employee_email', '?');
            $queryMaxDate = 'result2.employee_email = ?';
            $arrayBindings[] = $email;
            $arrayBindings[] = $email;
        }
        //get lastest test
        $result->whereRaw('result.created_at IN ('
            . 'SELECT MAX(result2.created_at) '
            . 'FROM ' . $tblResult . ' AS result2 '
            . 'INNER JOIN ' . $tblTest . ' AS test2 '
            . 'ON result2.test_id = test2.id '
            . 'WHERE '. $queryMaxDate .' '
            . 'AND result2.tester_type = ? '
            . 'GROUP BY test2.type_id'
            . ')')
            ->where('result.tester_type', '?');
        $arrayBindings[] = $testerType;
        $arrayBindings[] = $testerType;
        $result->setBindings($arrayBindings);

        $result->select(
                "result.total_corrects",
                "result.total_answers",
                "test_type.type",
                "test_type.type_name as name",
                DB::raw('IFNULL(result.total_question, test_type.count_question) AS total_questions')
        );
        $result->orderBy("result.created_at", 'desc');
        return $result->get();
    }

    /**
     * Get list result by email
     *
     * @param string $email
     * @param int $isAuth
     */
    public static function getListByEmail(
        $email,
        $isAuth = Test::IS_NOT_AUTH,
        $candidateId = null,
        $testerType = ViewTest::TESTER_PRIVATE
    )
    {
        $result = self::where('is_auth', $isAuth)
                    ->where('ntest_results.tester_type', $testerType)
                    ->join('ntest_tests', 'ntest_tests.id', '=', 'ntest_results.test_id')
                    ->leftJoin('ntest_types', 'ntest_tests.type_id', '=', 'ntest_types.id');
        if ($candidateId) {
            $result->where('ntest_results.candidate_id', $candidateId);
        } else {
            $result->where('ntest_results.employee_email', $email);
        }
        $result->select(
                "ntest_results.id",
                "total_corrects",
                "total_answers",
                "ntest_tests.type",
                "ntest_types.name",
                "ntest_results.created_at",
                DB::raw("(CASE WHEN ntest_results.total_question IS NULL THEN (SELECT COUNT(question_id) FROM ntest_test_question where test_id = ntest_tests.id) ELSE ntest_results.total_question END) AS total_questions")
        );
        $result->orderBy("ntest_results.created_at", 'desc');
        return $result->get();
    }

    /**
     * get test result by email and test id
     * @param type $email
     * @param type $test_id
     * @return type
     */
    public static function getByEmail($email, $testId = null, $testerType = ViewTest::TESTER_PRIVATE)
    {
        $result = self::where('employee_email', $email)
                ->where('tester_type', $testerType);
        if ($testId) {
            $result->where('test_id', $testId);
        }
        return $result->first();
    }

    /**
     * list test result by test_id
     * @param type $test_id
     * @return type
     */
    public static function listByTestId($testId, $testerType = ViewTest::TESTER_PRIVATE, $resultIds = null, $export = false)
    {
        $urlFilter = route('test::admin.test.results', $testId) . '/' . 'lists_tab';
        $pager = Config::getPagerData($urlFilter);
        $filterOrder = Form::getFilterPagerData('order', $urlFilter);
        $empTbl = Employee::getTableName();
        $teamTbl = Team::getTableName();
        $tmbTbl = TeamMember::getTableName();
        $rsTbl = self::getTableName();
        $test = Test::find($testId);

        $collection = self::from(DB::raw('(SELECT *, count(id) as count_data FROM ntest_results WHERE test_id = ' . $testId . ' AND tester_type = ' . $testerType . ' group by employee_email) as rs'))
            ->leftJoin($empTbl . ' as emp', 'emp.email', '=', 'rs.employee_email')
            ->leftJoin($tmbTbl . ' as tmb', 'tmb.employee_id', '=', 'emp.id')
            ->leftJoin($teamTbl . ' as team', 'team.id', '=', 'tmb.team_id')
            ->leftJoin('ntest_tests', 'ntest_tests.id', '=', 'rs.test_id')
            ->select(
                'rs.employee_email',
                'emp.id as employee_id',
                'rs.employee_name',
                DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
                'rs.count_data',
                'rs.phone',
                'rs.test_id',
                'ntest_tests.total_written_question',
                'rs.tester_type'
            )
            ->groupBy('rs.employee_email');

        $collection->leftJoin(
            DB::raw(
                '(SELECT rs1.* FROM ' . $rsTbl . ' as rs1 '
                . 'INNER JOIN '
                . '(SELECT employee_email, MAX(created_at) AS max_created_at FROM ' . $rsTbl . ' '
                . 'WHERE test_id = ' . $testId . ' AND tester_type = ' . $testerType .' GROUP BY employee_email ) AS rs2 '
                . 'ON rs1.employee_email = rs2.employee_email AND rs1.created_at = rs2.max_created_at '
                . 'WHERE test_id = ' . $testId . ' AND tester_type = ' . $testerType .') AS rs3'
            ),
            'rs.employee_email',
            '=',
            'rs3.employee_email'
        )
            ->addSelect(
                'rs3.id',
                'rs3.total_corrects',
                'rs3.total_answers',
                'rs3.total_question',
                'rs3.created_at',
                'rs3.updated_at',
                DB::raw('COALESCE(rs3.begin_at, "") AS begin_at'),
                DB::raw('IF(rs3.begin_at IS NULL, '.($test->time * 60).', TIME_TO_SEC(TIMEDIFF(rs3.created_at, rs3.begin_at))) AS total_finish_time')
            );

        // Filter team
        $teamSelected = Form::getFilterData('except', 'teams.id', $urlFilter);
        if (!empty($teamSelected)) {
            $collection->join($empTbl . ' as emp_filter', 'emp_filter.email', '=', 'rs.employee_email')
                ->join($tmbTbl . ' as tmb_filter', 'tmb_filter.employee_id', '=', 'emp.id')
                ->where('tmb_filter.team_id', $teamSelected);
        }
        self::filterGrid($collection, [], $urlFilter);
        if ($filterOrder) {
            if ($pager['order'] == 'created_at') {
                $pager['order'] = 'rs.created_at';
            }
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('rs.total_corrects', 'desc')
                ->orderBy('total_finish_time', 'asc')
                ->orderBy('rs.begin_at', 'asc');
        }
        if ($export) {
            if ($resultIds) {
                $collection->whereIn('rs.id', $resultIds);
            }
            return $collection->get();
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get more result by employee email
     * @return type
     */
    public function moreResults()
    {
        return $this->hasMany('\Rikkei\Test\Models\Result', 'employee_email', 'employee_email');
    }

    /**
     * get test history by candidate
     * @param type $email
     * @return type
     */
    public static function getHistoryByEmail($email, $testerType = ViewTest::TESTER_PRIVATE)
    {
        $testTbl = Test::getTableName();
        $resultTbl = self::getTableName();
        return Test::from($testTbl . ' as test')
                ->with([
                    'questions' => function ($query) {
                        $query->where('status', ViewTest::STT_ENABLE);
                    },
                    'subjectType'
                ])
                ->join($resultTbl . ' as result', function ($join) use ($email, $testerType) {
                    $join->on('test.id', '=', 'result.test_id')
                            ->where('employee_email', '=', $email)
                            ->where('result.tester_type', '=', $testerType);
                })
                ->select('test.id', 'test.name', 'test.time', 'test.type', 'test.type_id', 'test.url_code',
                        'result.id as result_id', 'result.employee_email', 'result.updated_at', 'result.total_corrects')
                ->groupBy('result.id')
                ->orderBy('result.updated_at', 'desc')
                ->get();
    }

    /**
     * get test that belongs to
     * @return type
     */
    public function test()
    {
        return $this->belongsTo('\Rikkei\Test\Models\Test', 'test_id');
    }

    /**
     * filter save random labels
     * @param type $value
     */
    public function setRandomLabelsAttribute($value)
    {
        if ($value && is_array($value)) {
            $value = serialize($value);
        } elseif (!$value) {
            $value = null;
        } else {
            //original
        }
        $this->attributes['random_labels'] = $value;
    }

    /**
     * filter get random labels
     * @param type $value
     * @return type
     */
    public function getRandomLabelsAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return unserialize($value);
    }

    /**
     * find (switch) test result in speicial language
     *
     * @param int $resultId
     * @param string $langCode
     * @return object
     */
    public static function findItemByLang($resultId, $langCode)
    {
        $resultTbl = self::getTableName();
        $groupTbl = LangGroup::getTableName();
        $aryLangCode = array_keys(CoreLang::allLang());
        $results = self::select($resultTbl . '.*', 'group.lang_code')
            ->join($groupTbl . ' as group', 'group.test_id', '=', $resultTbl . '.test_id')
            ->whereIn('group.lang_code', $aryLangCode)
            //maping with created_at date time
            ->whereIn($resultTbl . '.created_at', function ($query) use ($resultId, $groupTbl, $resultTbl) {
                $query->select('result.created_at')
                    ->from($groupTbl . ' as group')
                    ->join($resultTbl . ' as result', function ($join) use ($resultId) {
                        $join->on('result.test_id', '=', 'group.test_id')
                            ->where('result.id', '=', $resultId);
                    });
            })
            // Lọc theo email
            ->where($resultTbl . '.employee_email', function ($query) use ($resultId, $resultTbl) {
                $query->select($resultTbl . '.employee_email')
                    ->from($resultTbl)
                    ->where($resultTbl . '.id', $resultId);
            })
            ->get()
            ->groupBy('lang_code');

        if ($results->isEmpty()) {
            return null;
        }
        if (isset($results[$langCode])) {
            foreach ($results[$langCode] as $item) {
                if ($item->id == $resultId) {
                    return $item;
                }
            }
            return $results[$langCode]->first();
        }
        if (isset($results[CoreLang::DEFAULT_LANG])) {
            return $results[CoreLang::DEFAULT_LANG]->first();
        }
        return $results->first()->first();
    }

    /**
     * save submit resutl
     */
    public static function saveSubmitResult($testResult, $questions, $answers)
    {
        $resultCreate = self::create($testResult);

        $dataResultDetail = [];
        $testResult = array_only($testResult, TestResult::getFillableCols());
        $timeNow = Carbon::now()->toDateTimeString();
        foreach ($questions as $qItem) {
            // if not answer
            if (!isset($answers[$qItem->id]) || !$answers[$qItem->id]) {
                continue;
            }
            // init test result detail item
            $testResultItem = array_merge($testResult, [
                'test_result_id' => $resultCreate->id,
                'question_id' => $qItem->id,
                'answer_id' => null,
                'answer_content' => null,
                'is_correct' => false,
                'created_at' => $timeNow,
                'updated_at' => $timeNow,
            ]);
            // answer correct
            $isType2 = in_array($qItem->type, ViewTest::ARR_TYPE_2);
            $answerCorrect = $qItem->answers()->wherePivot('is_correct', '=', 1)->get();
            if (!$isType2 && $answerCorrect->isEmpty()) {
                continue;
            }
            // check type of question
            if (in_array($qItem->type, ViewTest::ARR_TYPE_1)) {
                $testResultItem['answer_content'] = trim($answers[$qItem->id]);
                $testResultItem['is_correct'] = false;
                if (in_array(mb_strtolower(trim($answers[$qItem->id])), array_map('mb_strtolower', $answerCorrect->lists('content')->toArray()))) {
                    $testResultItem['is_correct'] = true;
                }
                $dataResultDetail[] = $testResultItem;
                //$testResultItemCreate = TestResult::create($testResultItem);
            } elseif ($isType2) {
                $childs = $qItem->childs;
                if ($childs->isEmpty()) {
                    continue;
                }

                $ansChild = true;
                $hasAnsChild = false;
                $testResultChilds = [];
                // check correct child question
                foreach ($childs as $qChild) {
                    $ansChildCorrect = $qChild->answers()->wherePivot('is_correct', '=', 1)->first();
                    $ansChildId = null;
                    if (isset($answers[$qItem->id][$qChild->id]) && $answers[$qItem->id][$qChild->id]) {
                        $ansChildId = $answers[$qItem->id][$qChild->id];
                        if (!$hasAnsChild) {
                            $hasAnsChild = true;
                        }
                    } else {
                        if ($ansChild) {
                            $ansChild = false;
                        }
                        continue;
                    }
                    $resultItemChild = array_merge($testResultItem, [
                        'question_id' => $qChild->id,
                        'answer_id' => $ansChildId,
                        'is_correct' => false
                    ]);
                    if ($ansChildId != $ansChildCorrect->id) {
                        if ($ansChild) {
                            $ansChild = false;
                        }
                    } else {
                        $resultItemChild['is_correct'] = true;
                    }
                    $resultItemChildCreate = TestResult::create($resultItemChild);
                    //collect child id to update parent
                    array_push($testResultChilds, $resultItemChildCreate->id);
                }
                // check if answer least one child question
                if ($hasAnsChild) {
                    $testResultItem['is_correct'] = $ansChild;
                    $testResultItemCreate = TestResult::create($testResultItem);
                    // update parent id
                    TestResult::whereIn('id', $testResultChilds)
                            ->update(['parent_id' => $testResultItemCreate->id]);
                }
            } else {
                $ansId = null;
                $testResultItem['is_correct'] = false;
                if ($answers[$qItem->id]) {
                    $ansId = $answers[$qItem->id];
                    $ansCorrectArr = $answerCorrect->lists('id')->toArray();
                    $numTrue = 0;
                    foreach ($ansId as $aId) {
                        if (in_array($aId, $ansCorrectArr)) {
                            $numTrue ++;
                        }
                    }
                    if ($numTrue == count($ansCorrectArr) && $numTrue == count($ansId)) {
                        $testResultItem['is_correct'] = true;
                    }
                }
                $testResultItem['answer_content'] = serialize($ansId);
                $dataResultDetail[] = $testResultItem;
                //$testResultItemCreate = TestResult::create($testResultItem);
            }
        }
        if ($dataResultDetail) {
            TestResult::insert($dataResultDetail);
        }

        // collect result and insert result
        $resultCreate->total_corrects = TestResult::getTotalAnswerCorrect($resultCreate->id);
        $resultCreate->total_answers = TestResult::getTotalAnswer($resultCreate->id);
        $resultCreate->save();
        //will move to queue;
        event(new \Rikkei\Test\Event\SubmitTestEvent($resultCreate->id));
        //delete temp
        TestTemp::deleteTemp($resultCreate->employee_email, $resultCreate->test_id);

        return $resultCreate;
    }

    /**
     * list test result by test_id
     * @param int $testId
     *  @param string $employeeEmail
     *  @param int $testerType
     * @return array
     */
    public static function getListMoreResultByEmail ($testResultId)
    {
        $testResult = self::find($testResultId);
        $test = Test::find($testResult->test_id);
        return self::where('employee_email', $testResult->employee_email)
            ->where('tester_type', $testResult->tester_type)
            ->where('test_id', $testResult->test_id)
            ->where('id', '<>', $testResultId)
            ->select(
                'id',
                DB::raw('COALESCE(begin_at, "") AS begin_at'),
                'created_at',
                DB::raw('IF(begin_at IS NULL, ' . ($test->time * 60) . ', TIME_TO_SEC(TIMEDIFF(created_at, begin_at))) AS total_finish_time'),
                'total_answers',
                'total_corrects'
                )
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /*
     * sync language results, (will be moved to queue)
     */
    public function syncResultLang()
    {
        $testId = $this->test_id;
        $testDetails = $this->details;

        $testLangs = LangGroup::collectTestLangFromTestId($testId);
        if ($testLangs->isEmpty()) {
            return false;
        }

        $testLangIds = $testLangs->pluck('test_id')->toArray();
        $currQuesIds = $testDetails->pluck('question_id')->toArray(); //this result detail question ids
        $currDetailQOrders = TestQuestion::whereIn('question_id', $currQuesIds) //this question order where question ids in detail
            ->pluck('order', 'question_id')
            ->toArray();
        $currQuesChildIds = array_diff($currQuesIds, array_keys($currDetailQOrders));
        $currQuesParentChilds = [];
        $currQuesGroupParent = [];
        if ($currQuesChildIds) {
            $currCollectQParentChilds = Question::whereIn('id', $currQuesChildIds)->get();
            $currQuesParentChilds = $currCollectQParentChilds->pluck('parent_id', 'id')->toArray();
            $currQuesGroupParent = $currCollectQParentChilds->groupBy('parent_id')
                ->map(function ($childs) {
                    return $childs->pluck('id');
                })
                ->toArray();
        }
        //get other question of test language
        $listTestQuestions = TestQuestion::select('test_id', 'question_id', 'order')
            ->whereIn('test_id', $testLangIds)
            ->get();
        $groupTestQuestions = $listTestQuestions->groupBy('test_id');

        //this result question index
        $questionIndexs = $this->question_index;
        if ($questionIndexs) {
            $aryQuesIndexs = explode(',', $questionIndexs);
            $aryQuesOrders = TestQuestion::whereIn('question_id', $aryQuesIndexs)
                ->orderBy(DB::raw('FIELD(question_id, '. $questionIndexs .')'))
                ->pluck('question_id', 'order')
                ->toArray();
        }
        //this random labels
        $randomLabels = $this->random_labels;
        $currAnswers = Question::getListAnswers($currQuesIds);
        $rsDetailData = [];
        $dataQuestionParent = [];
        $quesParentChilds = [];
        if ($currQuesChildIds) {
            $quesParentChilds = Question::whereIn('parent_id', $listTestQuestions->pluck('question_id')->toArray())
                ->get()
                ->groupBy('parent_id')
                ->map(function ($childs) {
                    return $childs->pluck('id');
                })
                ->toArray();
        }

        foreach ($testLangs as $testLang) {
            $testQuestions = $groupTestQuestions[$testLang->test_id];
            if ($testQuestions->isEmpty()) {
                continue;
            }

            $questionOrders = $testQuestions->pluck('question_id', 'order')->toArray();
            if (isset($aryQuesOrders)) {
                //arange question order base on aryQuesOrders;
                $newQOrders = [];
                foreach ($aryQuesOrders as $order => $qId) {
                    if (isset($questionOrders[$order])) {
                        $newQOrders[$order] = $questionOrders[$order];
                    }
                }
                $questionOrders = $newQOrders;
            }
            //result data
            $testLangResultData = ['test_id' => $testLang->test_id];
            foreach ($this->colsSync as $col) {
                $testLangResultData[$col] = $this->{$col};
            }
            if ($questionIndexs && $questionOrders) {
                $testLangResultData['question_index'] = implode(',', $questionOrders);
            }
            //ansewers of question in this test lang
            $langAnswers = Question::getListAnswers($questionOrders, false);
            $originLangAnswers = Question::getListAnswers($questionOrders, false); //performance < clone
            if ($randomLabels) {
                $currAnsQuesIds = $questionIndexs ? $aryQuesOrders : null;
                if (!$currAnsQuesIds) {
                    $currAnsQuesIds = TestQuestion::where('test_id', $testId)
                        ->pluck('question_id', 'order')
                        ->toArray();
                }
                if (!$currAnsQuesIds) {
                    self::create($testLangResultData);
                    continue;
                }
                $langRandAnswers = [];
                $questionOrderValues = array_values($questionOrders);
                foreach ($currAnsQuesIds as $order => $qId) {
                    if (!isset($questionOrders[$order])) {
                        continue;
                    }
                    $qLangId = $questionOrders[$order];
                    //random answer base $randomLabels
                    $langRandAnswers[$qLangId] = ViewTest::shuffleAnswers(
                        $langAnswers[$qLangId],
                        $randomLabels,
                        $questionOrderValues,
                        $currAnswers,
                        $qLangId
                    )['save'];
                }
                if ($langRandAnswers) {
                    $testLangResultData['random_labels'] = $langRandAnswers;
                }
            }
            $testLangResult = self::create($testLangResultData);
            if (!isset($dataQuestionParent[$testLang->test_id])) {
                $dataQuestionParent[$testLang->test_id] = [];
            }
            if (!isset($dataQuestionParent[$testLangResult->id])) {
                $dataQuestionParent[$testLang->test_id][$testLangResult->id] = [];
            }
            if (!$testDetails->isEmpty()) {
                foreach ($testDetails as $detail) {
                    $detailQId = $detail->question_id;
                    $rsDataItem = [
                        'test_result_id' => $testLangResult->id,
                        'test_id' => $testLang->test_id,
                        'is_correct' => $detail->is_correct,
                        'created_at' => $detail->created_at,
                        'updated_at' => $detail->updated_at,
                    ];
                    $isQChild = false;
                    if (isset($currDetailQOrders[$detailQId])) {
                        $qOrder = $currDetailQOrders[$detailQId];
                    } else {
                        $isQChild = true;
                        //child question
                        $currQParentId = $currQuesParentChilds[$detailQId];
                        $qOrder = $currDetailQOrders[$currQParentId];
                        $qKeyChild = array_search($detailQId, $currQuesGroupParent[$currQParentId]);
                        $detailQId = $currQParentId;
                    }
                    if (!isset($questionOrders[$qOrder])) {
                        continue;
                    }
                    $questId = $questionOrders[$qOrder]; //this question lang id
                    $rsDataItem['question_id'] = $questId;
                    if ($detail->parent_id && $isQChild && $qKeyChild !== false) {
                        if (isset($quesParentChilds[$questId][$qKeyChild])) { // $questId --> question parent id
                            if (!isset($dataQuestionParent[$testLang->test_id][$testLangResult->id][$questId])) {
                                $dataQuestionParent[$testLang->test_id][$testLangResult->id][$questId] = [];
                            }
                            $dataQuestionParent[$testLang->test_id][$testLangResult->id][$questId][] = $quesParentChilds[$questId][$qKeyChild];
                            $rsDataItem['question_id'] = $quesParentChilds[$questId][$qKeyChild];
                        }
                    }
                    $rsDataItem['answer_id'] = null;
                    $rsDataItem['answer_content'] = null;
                    if (isset($currAnswers[$detailQId]) && isset($originLangAnswers[$questId])) {
                        $currQuesAnswers = $currAnswers[$detailQId]->groupBy('id');
                        $langQuesAnswers = $originLangAnswers[$questId]->groupBy('label');
                        if ($detail->answer_id && isset($currQuesAnswers[$detail->answer_id])) {
                            $currAnsLabel = $currQuesAnswers[$detail->answer_id]->first()->label;
                            if (isset($langQuesAnswers[$currAnsLabel])) {
                                $rsDataItem['answer_id'] = $langQuesAnswers[$currAnsLabel]->first()->id;
                            }
                        }
                        if ($detail->answer_content) {
                            if (CoreView::isSerialized($detail->answer_content)) {
                                $aryDetailAns = unserialize($detail->answer_content);
                                $aryDetailLangAns = [];
                                foreach ($aryDetailAns as $detailAnsId) {
                                    if (isset($currQuesAnswers[$detailAnsId])) {
                                        $detailAnsLabel = $currQuesAnswers[$detailAnsId]->first()->label;
                                        if (isset($langQuesAnswers[$detailAnsLabel])) {
                                            $aryDetailLangAns[] = $langQuesAnswers[$detailAnsLabel]->first()->id;
                                        }
                                    }
                                }
                                if ($aryDetailLangAns) {
                                    $rsDataItem['answer_content'] = serialize($aryDetailLangAns);
                                }
                            } else {
                                $rsDataItem['answer_content'] = $detail->answer_content;
                            }
                        }
                    }
                    $rsDetailData[] = $rsDataItem;
                }
            }
        }
        if ($rsDetailData) {
            TestResult::insert($rsDetailData);
        }
        //update parent
        if ($dataQuestionParent) {
            self::updateParentResultDetail($dataQuestionParent);
        }
    }

    /*
     * update parent result detail
     * @param $dataQuestionParent [testId => [resultId => [parentQuestionId => [arary child question id]]]
     */
    public static function updateParentResultDetail($dataQuestionParent)
    {
        foreach ($dataQuestionParent as $testId => $dataTestResult) {
            foreach ($dataTestResult as $resultId => $aryParents) {
                foreach ($aryParents as $qParentId => $aryQChildIds) {
                    $resultParent = TestResult::where('test_id', $testId)
                        ->where('test_result_id', $resultId)
                        ->where('question_id', $qParentId)
                        ->first();
                    if ($resultParent) {
                        TestResult::where('test_id', $testId)
                            ->where('test_result_id', $resultId)
                            ->whereIn('question_id', $aryQChildIds)
                            ->update(['parent_id' => $resultParent->id]);
                    }
                }
            }
        }
    }
}
