<?php

namespace Rikkei\Test\Http\Controllers;

use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Question;
use Rikkei\Test\Models\TestResult;
use Rikkei\Test\Models\TestTemp;
use Rikkei\Test\Models\Result;
use Rikkei\Magazine\Model\ImageModel;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Test\Models\Type;
use Rikkei\Test\Models\WrittenQuestion;
use Rikkei\Test\Models\WrittenQuestionAnswer;
use Rikkei\Test\View\ViewTest;
use Carbon\Carbon;
use Validator;
use Session;
use Storage;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\RicodeTest;
use Rikkei\Core\View\CoreLang;
use Rikkei\Core\View\CurlHelper;

class TestController extends Controller
{

    /**
     * test index
     * @return type
     */
    public function index() {
        return view('test::test.index');
    }

    /**
     * swith test language
     * @param Request $request
     * @return redirect back
     */
    public function switchLang(Request $request)
    {
        $langCode = $request->get('lang');
        if ($langCode) {
            CoreLang::switchLang($langCode);
        }
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(true);
        }
        return redirect()->back()
                    ->withInput();
    }

    /**
     * check test password
     * @param Request $request
     * @return type
     */
    public function checkAuth(Request $request) {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.email')]),
            'email.email' => trans('test::validate.email_not_valid')
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $email = $request->get('email');
        //find candidate
        $candidate = Candidate::select('id', 'email', 'fullname', 'mobile', 'test_option_type_ids')
                ->where('email', $email)
                ->orderBy('id', 'desc')
                ->first();
        
        $exam_url = isset($candidate->ricodeTest) ? config('app.ricode_app_url').$candidate->ricodeTest->url : null;

        if (!$candidate) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('test::validate.candidate_not_found')]]);
        }
        $tests = Result::getHistoryByEmail($candidate->email);
        $hasHistory = !$tests->isEmpty();
        return view('test::test.confirm_info', compact('candidate', 'hasHistory', 'exam_url'));
    }
    
    /*
     * Candidate testing
     */
    public function candidateTest(Request $request)
    {
        if ($request->method() != 'POST') {
            return redirect()->route('test::index');
        }
        $valid = Validator::make($request->all(), [
            'candidate_id' => 'required|numeric',
            'test_type' => 'required'
        ]);
        
        if ($valid->fails()) {
            return view('errors.general', ['errors' => $valid->errors()]);
        }
        $candidate = Candidate::find($request->get('candidate_id'), ['id', 'fullname', 'email', 'test_option_type_ids']);
        $testTypes = $candidate->listTestTypes();
        $hasGmat = in_array(Type::GMAT_CODE, $testTypes->pluck('code')->toArray());
        $hasGmat = false;
        $email = $candidate->email;
        $testType = $request->get('test_type');

        $testTemps = TestTemp::getAllTestTempByType($email, $testType);
        $testTemp = null;
        $otherTemp = null; //other test temp has same group language
        $langCode = Session::get('locale');
        if (!$testTemps->isEmpty()) {
            $testTempsLang = $testTemps->groupBy('lang_code');
            if (isset($testTempsLang[$langCode])) {
                $testTemp = $testTempsLang[$langCode]->first();
            } elseif (isset($testTempsLang[CoreLang::DEFAULT_LANG])) {
                $otherTemp = $testTempsLang[CoreLang::DEFAULT_LANG]->first();
            } else {
                $otherTemp = $testTemps->first();
            }
        }

        $test = null;
        if ($testTemp) {
            $test = Test::find($testTemp->test_id);
            if ($test) {
                ViewTest::checkValidTime($test);
                $test->group_id = $testTemp->group_id;
            }
        }

        if (!$test || Result::checkDoTest($email, $test, $candidate->id)) {
            //get list test that not done by candidate
            $listTests = Test::getTestsNotDone($email, $testType, $candidate->id);

            if ($listTests->count() < 1) {
                return view('errors.general', ['message' => trans('test::validate.test_not_found_or_tested')]);
            }
            //not valid test time
            $listTests = $listTests->filter(function ($fTest) use ($otherTemp) {
                if (ViewTest::checkValidTime($fTest, true)) {
                    return false;
                }
                if ($otherTemp && $otherTemp->group_id != $fTest->group_id) {
                    return false;
                }
                return true;
            });
            $test = $listTests->random();
            //check valid time
            ViewTest::checkValidTime($test);
            $dataTempCreate = [
                'employee_email' => $email,
                'test_id' => $test->id
            ];
            $testTemp = TestTemp::updateOrCreate(
                $dataTempCreate,
                $otherTemp ? array_only($otherTemp->toArray(), $otherTemp->colsSync) : []
            );
        }

        if ($hasGmat) {
            $doingGmat = Type::where('id', $testType)
                ->where('code', Type::GMAT_CODE)
                ->first();

            if (!$doingGmat) {
                //check xem đã làm bài GMAT chưa
                $GmatResult = Result::select(DB::raw('MAX(ntest_results.id) as result_id'), 'ntest_tests.min_point', 'total_corrects')
                    ->join('ntest_tests', 'ntest_tests.id', '=', 'ntest_results.test_id')
                    ->join('ntest_types', function ($join) use ($testTypes) {
                        $join->on('ntest_types.id', '=', 'ntest_tests.type_id')
                            ->where('ntest_types.code', '=', Type::GMAT_CODE)
                            ->whereIn('ntest_types.id', $testTypes->pluck('id')->toArray());
                    })
                    ->where('candidate_id', $candidate->id)
                    ->whereDate('ntest_results.created_at', '=', date("Y-m-d"))
                    ->first();

                if (!$GmatResult->result_id || $GmatResult->total_corrects < $GmatResult->min_point) {
                    return view('errors.general', ['message' => trans('test::test.You have not completed the test', ['test' => 'GMAT'])]);
                }
            }
        }

        $questions = $test->questions()
                ->with(['answers', 'childs', 'categories'])
                ->where('status', ViewTest::STT_ENABLE)
                ->get();

        $writtenQuestions = WrittenQuestion::getCollectWrittenQuestion($test->id, ViewTest::STT_ENABLE);
        $writtenArrayKey = Session::get('writtenQuestions_' . $test->group_id);
        if ($writtenArrayKey) {
            $writtenQuestions->whereIn('ntest_written_questions.id', $writtenArrayKey);
        } else {
            if ($test['written_cat']) {
                $writtenQuestions->where('cat_id', $test['written_cat']);
            }
            if ($test['total_written_question']) {
                $writtenQuestions->limit($test['total_written_question']);
            }
            $writtenQuestions->inRandomOrder();
        }
        $writtenQuestions = $writtenQuestions->get();
        if (isset($test['total_written_question']) && $test['total_written_question'] == 0) {
            $writtenQuestions = [];
        }
        $noWritten = count($writtenQuestions) > 0 ? false : true;

        //testing view page
        return view('test::test.view', compact('test', 'testTemp', 'candidate', 'questions', 'writtenQuestions', 'noWritten'));
    }
    
    /**
     * Candidate tested history
     */
    public function candidateHistory(Request $request) {
        $candidateId = $request->get('candidate_id');
        $candidate = Candidate::find($candidateId, ['id', 'email', 'fullname']);
        if (!$candidate) {
            abort(404);
        }
        $tests = Result::getHistoryByEmail($candidate->email);
        return view('test::test.history', compact('candidate', 'tests'));
    }
    
    /**
     * get tests by type_id
     * @param Request $request
     * @return type
     */
    public function getByType(Request $request) {
        $valid = Validator::make($request->all(), [
            'type_id' => 'required|numeric',
            'is_group' => 'required|numeric'
        ]);
        if ($valid->fails()) {
            return response()->json(null, 422);
        }
        $typeId = $request->get('type_id');
        $isGroup = $request->get('is_group');
        $collection = Test::where('is_auth', 0);
        if ($isGroup) {
            $collection->where('type', $typeId);
        } else {
            $collection->where('type_id', $typeId);
        }
        $collection = $collection->orderBy('created_at', 'desc')->get();
        $results = '<option value="">--'. trans('test::test.select_test') .'--</option>';
        if (!$collection->isEmpty()) {
            foreach ($collection as $item) {
                $results .= '<option value="'. $item->url_code .'">'. $item->name .'_'. $item->created_at->format('d-m-Y') . ' (' . $item->time . trans('test::test.minute') .')</option>';
            }
        }
        return $results;
    }
    
    /**
     * preview test
     * @param Request $request
     * @return type
     */
    public function getShowTest($code, Request $request)
    {
        $langCode = $request->get('locale');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $test = Test::findItemByLang($code, $langCode, true);
        if (!$test) {
            abort(404);
        }
        if ($test->url_code != $code) {
            return redirect()->route('test::view_test', ['code' => $test->url_code]);
        }

        //check valid time to test
        ViewTest::checkValidTime($test);

        $hasTested = false;
        $startText = trans('test::test.start_test');
        if ($test->is_auth) {
            if (!auth()->check()) {
                Session::put('curUrl', route('test::view_test', ['code' => $code]));
                return redirect()->route('core::home');
            }
            $user = Permission::getInstance()->getEmployee();
            if (TestTemp::checkTest($user->email, $test->id)) {
                $startText = trans('test::test.contineu_test');
            }
            if ($resultId = Result::checkDoTest($user->email, $test)) {
                $hasTested = $resultId;
            }
        }

        return view('test::test.info', compact('test', 'hasTested', 'startText'));
    }
    
    /**
     * view to test
     * @param type $code
     * @return type
     */
    public function view($code, Request $request)
    {
        $langCode = $request->get('locale');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $test = Test::findItemByLang($code, $langCode, true);
        if (!$test) {
            abort(404);
        }
        if ($test->url_code != $code) {
            $reqParams = ['code' => $test->url_code];
            if (!$test->is_auth) {
                $reqParams = array_merge($reqParams, $request->except(['_token']));
            }
            return redirect()->route('test::view', $reqParams);
        }

        //check valid time to test
        ViewTest::checkValidTime($test);

        $candidate = null;
        $keyPerson = null;
        $person = null;
        $dataTestTemp = [];
        if ($test->is_auth) {
            if (!auth()->check()) {
                Session::put('curUrl', route('test::view_test', ['code' => $code]));
                return redirect()->route('core::home');
            }
            $user = Permission::getInstance()->getEmployee();
            $email = $user->email;
            if ($resultId = Result::checkDoTest($email, $test)) {
                return view('test::test.info', compact('test', 'email'))->with('hasTested', $resultId);
            }
            $dataTestTemp = [
                'employee_id' => $user->id,
                'employee_name' => $user->name,
                'employee_email' => $user->email
            ];
        } else {
            if ($request->has('candidate')) {
                $candidate = Candidate::find($request->get('candidate'), ['id', 'fullname', 'email']);
                if (!$candidate) {
                    return view('errors.general', ['message' => trans('test::test.input_infor_invalid')]);
                }
                $email = $candidate->email;
                if ($candidate && ($resultId = Result::checkDoTest($candidate->email, $test, $candidate->id))) {
                    return view('test::test.info', compact('test', 'email'))->with('hasTested', $resultId);
                }
                $dataTestTemp = [
                    'candidate_id' => $candidate->id,
                    'employee_name' => $candidate->fullname,
                    'employee_email' => $candidate->email
                ];
            } elseif ($request->has('person')) {
                $person = $request->get('person');
                $valid = Validator::make($person, [
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required'
                ], [
                    'name.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.full_name')]),
                    'email.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.email')]),
                    'email.email' => trans('test::validate.error_email_format'),
                    'phone.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.phone_number')])
                ]);
                //not valid
                if ($valid->fails()) {
                    return redirect()->back()->withInput()->withErrors($valid->errors());
                }
                //check do test
                $email = $person['email'];
                if ($resultId = Result::checkDoTest($email, $test, null, ViewTest::TESTER_PUBLISH)) {
                    return view('test::test.info', compact('test', 'email'))->with('hasTested', $resultId);
                }
                $keyPerson = md5($email);
                Session::put($keyPerson, $person);
                $dataTestTemp = [
                    'employee_name' => $person['name'],
                    'employee_email' => $person['email']
                ];
            } else {
                return view('errors.general', ['message' => trans('test::test.input_infor_invalid')]);
            }
        }
        //check testtemp
        $testTemps = TestTemp::getAlltestTempsByTestId($email, $test->id);
        $testTemp = null;
        $otherTemp = null; //other test temp has same group language
        if (!$testTemps->isEmpty()) {
            $testTempsLang = $testTemps->groupBy('lang_code');
            if (isset($testTempsLang[$langCode])) {
                $testTemp = $testTempsLang[$langCode]->first();
            } elseif (isset($testTempsLang[CoreLang::DEFAULT_LANG])) {
                $otherTemp = $testTempsLang[CoreLang::DEFAULT_LANG]->first();
            } else {
                $otherTemp = $testTemps->first();
            }
        }

        $questions = $test->questions()
                ->with(['answers', 'childs', 'categories'])
                ->where('status', ViewTest::STT_ENABLE)
                ->get();
        $writtenQuestions = WrittenQuestion::getCollectWrittenQuestion($test->id, ViewTest::STT_ENABLE);
        $writtenArrayKey = Session::get('writtenQuestions_' . $test->group_id);
        if ($writtenArrayKey) {
            $writtenQuestions->whereIn('ntest_written_questions.id', $writtenArrayKey);
        } else {
            if ($test['written_cat']) {
                $writtenQuestions->where('cat_id', $test['written_cat']);
            }
            if ($test['total_written_question']) {
                $writtenQuestions->limit($test['total_written_question']);
            }
            $writtenQuestions->inRandomOrder();
        }
        $writtenQuestions = $writtenQuestions->get();
        if (isset($test['total_written_question']) && $test['total_written_question'] == 0) {
            $writtenQuestions = [];
        }
        $idTest = $test->id; 
        if(Session::get('timeStart_'. $idTest) == null) {
            $testTime = $test->time;
            $timeStart = Carbon::now()->addMinutes($testTime)->getTimestamp();
            Session::put('timeStart_'. $idTest,$timeStart);
            Session::put('timeBegin_'. $idTest,Carbon::now()->getTimestamp());
        } 
        $noWritten = count($writtenQuestions) > 0 ? false : true;

        return view('test::test.view', compact('test', 'testTemp', 'candidate', 'questions', 'keyPerson', 'person', 'noWritten', 'writtenQuestions'));
    }
    
    /**
     * check did test by email
     * @param Request $request
     * @return type
     */
    public function checkDoTest(Request $request) {
        $valid = Validator::make($request->all(), [
            'test_id' => 'required',
            'email' => 'required|email'
        ]);
        if ($valid->fails()) {
            return response()->json(['check' => false]);
        }
        $testId = $request->get('test_id');
        $test = Test::find($testId);
        if (!$test) {
            return response()->json(['check' => false]);
        }
        $email = $request->get('email');
        $check = Result::checkDoTest($email, $test);
        return response()->json([
                'check' => $check, 
                'message' => trans('test::test.you_had_done_this_test'). 
                    ',  <a class="link" href="'. route('test::result', ['id' => $check]) .'">'. 
                    trans('test::test.view_results') .'</a>'
            ]);
    }
    
    /**
     * submit test
     * @param Request $request
     * @return type
     */
    public function submitTest(Request $request) {
        $testId = $request->get('test_id');
        $isPost = ($request->method() == 'POST');
        if (!$testId) {
            if ($isPost) {
                return view('errors.general', ['message' => trans('test::validate.na_error')]);
            }
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('test::validate.na_error')]]);
        }
        $test = Test::with('langGroup')->find($testId);
        if (!$test) {
            if ($isPost) {
                return view('errors.general', ['message' => trans('test::validate.test_not_found')]);
            }
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('test::validate.test_not_found')]]);
        }

        if(Session::get('timeStart_'. $testId) == null) {
            $testTime = $test->time;
            $timeStart = Carbon::now()->addMinutes($testTime)->getTimestamp();
            Session::put('timeStart_'. $testId,$timeStart);
        } 
        Session::forget('timeStart_'. $testId);

        $keyQuestionIndex = 'question_index_' . $test->langGroup->group_id;
        $writtenArrayKey = 'writtenQuestions_' . $test->langGroup->group_id;
        $testResult = [];
        $writtenIndex = Session::get($writtenArrayKey);
        $writtenQuestions = null;
        $writtenAnswers = isset($request->get('answers')['written']) ? $request->get('answers')['written'] : [];
        $writtenAnswers = array_filter($writtenAnswers);
        $testResult['written_index'] = $writtenIndex ? implode(',', $writtenIndex) : implode(',', array_keys($writtenAnswers));
        $testResult['test_id'] = $testId;
        if ($request->get('question_index')) {
            $testResult['question_index'] = $request->get('question_index');
        }
        $testResult['total_question'] = $request->get('total_question');

        $candidateId = $request->get('candidate');
        if (!$test->is_auth) {
            //check has candidate
            if (!$candidateId) {
                //get person information
                $keyPerson = $request->get('key_person');
                $person = Session::get($keyPerson);
                if (!$keyPerson || !$person) {
                    return view('errors.general', ['message' => trans('test::test.input_infor_invalid')]);
                }
                $testResult = array_merge($testResult, [
                    'employee_name' => $person['name'],
                    'employee_email' => $person['email'],
                    'phone' => $person['phone'],
                    'tester_type' => ViewTest::TESTER_PUBLISH,
                ]);
            } else {
                //get candidate
                $candidate = Candidate::find($candidateId, ['id', 'fullname', 'email']);
                if (!$candidate) {
                    if ($isPost) {
                        return view('errors.general', ['message' => trans('test::validate.candidate_not_found')]);
                    }
                    return redirect()->back()
                            ->with('messages', ['errors' => [trans('test::validate.candidate_not_found')]]);
                }
                $testResult = array_merge($testResult, [
                    'employee_name' => $candidate->fullname,
                    'employee_email' => $candidate->email,
                    'candidate_id' => $candidate->id,
                ]);
                $candidateId = $candidate->id;
            }
        } else {
            $employee = Permission::getInstance()->getEmployee();
            $testResult = array_merge($testResult, [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_email' => $employee->email,
            ]);
            $candidateId = $employee->id;
        }
        // check do this test
        if ($resultId = Result::checkDoTest(
            $testResult['employee_email'],
            $test,
            !$test->is_auth ? $candidateId : null,
            isset($testResult['tester_type']) ? $testResult['tester_type'] : ViewTest::TESTER_PRIVATE
        )) {
            if ($isPost) {
                return view('errors.general', ['testedId' => $resultId]);
            }
            return redirect()->back()->withInput()->with('tested', $resultId);
        }
        
        //save random answer
        if ($test->random_answer) {
            $keyRandLabel = $request->get('key_rand_answer');
            $aryRanAnsLabels = Session::get($keyRandLabel);
            $testResult['random_labels'] = $aryRanAnsLabels && isset($aryRanAnsLabels[$test->id]) ? $aryRanAnsLabels[$test->id] : null;
        }

        //get answers
        $answers = $request->get('answers');
        $testTemp = TestTemp::getTemp($testResult['employee_email'], $testId);
        if (!$testTemp) {
            $testTemp = TestTemp::getAlltestTempsByTestId($testResult['employee_email'], $testId)->first();
        }
        if ($testTemp) {
            $testResult['begin_at'] = $testTemp->created_at;
        }
        $idTest = $test->id;
        $data = Session::get('timeBegin_'. $idTest);
        $testResult['begin_at'] = date("Y-m-d H:i:s",$data);
        if (!$answers || count($answers) < 1) {
            $resultCreate = Result::create($testResult);
            Session::forget($keyQuestionIndex);
            Session::forget($writtenArrayKey);
            if ($test->random_answer) {
                Session::forget($keyRandLabel);
            }
            $textWarning = trans('test::test.result_test'). ' ' .trans('test::test.back_retest');
            Session::flash('textWarning', $textWarning);
            event(new \Rikkei\Test\Event\SubmitTestEvent($resultCreate->id));
            TestTemp::deleteTemp($resultCreate->employee_email, $resultCreate->test_id);
            //return to result page
            return redirect()->route('test::result', ['id' => $resultCreate->id]);
        }

        // Check written question
        if (!empty($writtenAnswers)) {
            $writtenQuestions = WrittenQuestion::listWrittenQuestion(array_keys($writtenAnswers), ViewTest::STT_ENABLE);
        }

        $questions = $test->questions()
                ->where('status', ViewTest::STT_ENABLE)
                ->get();
        if ($questions->isEmpty() && !$writtenQuestions) {
            if ($isPost) {
                return view('errors.general', ['message' => trans('test::test.no_questions')]);
            }
            return redirect()->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('test::test.no_questions')]]);
        }
        
        DB::beginTransaction();
        //save test result
        $testResult['leaved_at'] = Carbon::now()->toDateTimeString();
        try {
            $resultCreate = Result::saveSubmitResult($testResult, $questions, $answers);
            if ($writtenQuestions) {
                WrittenQuestionAnswer::saveSubmitWrittenResult($testResult, $writtenAnswers, $resultCreate->id);
            }
            DB::commit();

            //Call API HRM
            if ($test->is_auth == Test::IS_AUTH) {
                $employee = Permission::getInstance()->getEmployee();
                $emailAPI = $employee->email;
                $dtTest = Test::find($testId);
                $dtResultMax = Result::whereNotIn('id', [$resultCreate->id])->where('employee_email', $emailAPI)->where('test_id', $testId)->max('total_corrects');
                $isCallApi = false;
                $dataAPI = [
                    'email' => $emailAPI,
                    'code' => $dtTest->url_code,
                    'result' => ($dtTest->min_point && $resultCreate->total_corrects < $dtTest->min_point) ? 0 : 1,
                    'point' => $resultCreate->total_corrects,
                    'total_question' => $resultCreate->total_question,
                ];
                if ($dtResultMax || $dtResultMax == '0') {
                    if ($resultCreate->total_corrects > $dtResultMax) {
                        $isCallApi = true;
                    }
                } else {
                    $isCallApi = true;
                }
                
                if ($isCallApi) {
                    $this->callTestsApiHRM($dataAPI);
                }
            }

            Session::forget($keyQuestionIndex);
            Session::forget($writtenArrayKey);
            if ($test->random_answer) {
                Session::forget($keyRandLabel);
            }
            if (isset($keyPerson)) {
                Session::forget($keyPerson);
            }

            if ($test->set_min_point == Test::SET_MIN_POINT
                && $test->valid_view_time == Test::SET_VALID_TIME
                && $test->min_point
                && $resultCreate->total_corrects < $test->min_point
            ) {
                $textWarning = trans('test::test.result_test'). ' ' .trans('test::test.back_retest');
                Session::flash('textWarning', $textWarning);
            }
            //return to result page
            return redirect()->route('test::result', ['id' => $resultCreate->id]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            if ($isPost) {
                return view('errors.general', ['message' => trans('test::validate.na_error_or_submited_test')]);
            }
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('test::validate.na_error_or_submited_test')]]);
        }
    }

    public function callTestsApiHRM($params)
    {
        $token = config('api.token_api_hrm');
        $header = [
            "Authorization: Bearer {$token}",
        ];
        $url = 'https://hrm.rikkei.vn/api/management/integration/result-test';

        $response = CurlHelper::httpPut($url, $params, $header);
        $response = json_decode($response, true);

        if (isset($response['status']) && $response['status'] == 'ERROR') {
            \Log::info('Call API HRM Tests - No response data');
            \Log::info(print_r($response, true));
        }
        return $response;
    }

    /**
     * view test results
     * @param $resultId test_result id
     * @return type
     */
    public function testResult($resultId)
    {
        $langCode = Session::get('locale');
        $testResult = Result::findItemByLang($resultId, $langCode);
        if (!$testResult) {
            abort(404);
        }
        if ($resultId != $testResult->id) {
            return redirect()->route('test::result', ['id' => $testResult->id]);
        }
        $test = $testResult->test;

        if (!$test) {
            abort(404);
        }
        //check permission + over time
        if (auth()->check()) {
            //check is not admin
            if (!ViewTest::hasPermiss($test->created_by, 'test::admin.test.edit')) {
                //if is not public test
                if ($test->is_auth && Permission::getInstance()->getEmployee()->email != $testResult->employee_email) {
                    return view('test::test.view_error', ['message' => trans('test::test.You do not have permission')]);
                }
//                $leaved_at = $testResult->leaved_at ? $testResult->leaved_at : $testResult->updated_at;
//                if (Carbon::now()->subMinutes(Test::VIEW_RESULT_TIME)->gt($leaved_at)) {
//                    return view('test::test.view_error', ['message' => trans('test::test.Over time see result')]);
//                }
            }
        } else {
            //check test need auth
            if ($test->is_auth) {
                Session::put('curUrl', route('test::result', ['id' => $resultId]));
                return redirect()->route('core::home');
            }
            $leaved_at = $testResult->leaved_at ? $testResult->leaved_at : $testResult->updated_at;
            if (Carbon::now()->subMinutes(Test::VIEW_RESULT_TIME)->gt($leaved_at)) {
                return view('test::test.view_error', ['message' => trans('test::test.Over time see result')]);
            }
        }
        $writtenDetail = null;
        if ((int)$test->show_detail_answer !== Test::SHOW_RESULT_ONLY) {
            $testResultTbl = TestResult::getTableName(); //result detail
            $questionTbl = Question::getTableName();
            if (!is_null($testResult->question_index)) {
                $resultDetail = $test->questions(false);
                $question_index = explode(',', $testResult->question_index);
                $resultDetail->whereIn($questionTbl . '.id', $question_index)
                    ->orderBy(DB::raw('FIELD('. $questionTbl .'.id,'. $testResult->question_index .')'), 'asc');
            } else {
                $resultDetail = $test->questions(true);
            }
            $resultDetail->leftJoin($testResultTbl.' as trs', function ($join) use ($questionTbl, $resultId) {
                $join->on($questionTbl.'.id', '=', 'trs.question_id')
                    ->where('trs.test_result_id', '=', $resultId);
            })
                ->where('status', ViewTest::STT_ENABLE)
                ->with('answers')
                ->with('childs')
                ->select($questionTbl.'.*', 'trs.test_id', 'trs.answer_id', 'trs.answer_content', 'trs.is_correct')
                ->groupBy($questionTbl.'.id');

            $resultDetail = $resultDetail->get();
            // get written questions and answer
            $writtenDetail = collect();
            $writtenIndex = array_map('intval', explode(',', $testResult->written_index));
            if ($writtenIndex) {
                $writtenQuestionTbl = WrittenQuestion::getTableName();
                $writtenQuestionAnswerTbl = WrittenQuestionAnswer::getTableName();
                $writtenDetail = WrittenQuestion::getCollectWrittenQuestion($test->id, ViewTest::STT_ENABLE)
                    ->leftJoin($writtenQuestionAnswerTbl . ' as written', function ($join) use ($writtenQuestionTbl, $resultId) {
                        $join->on($writtenQuestionTbl . '.id', '=', 'written.written_id')
                            ->where('written.result_id', '=', $resultId);
                    })
                    ->whereIn($writtenQuestionTbl . '.id', $writtenIndex)
                    ->select($writtenQuestionTbl . '.test_id', $writtenQuestionTbl . '.id', 'content as question', 'answer')
                    ->groupBy($writtenQuestionTbl . '.id')
                    ->orderBy($writtenQuestionTbl . '.id', 'asc')
                    ->get();
            }

            if ((int)$test->show_detail_answer === Test::SHOW_WRONG_ANSWER) {
                foreach ($resultDetail as $key => $item) {
                    $item['number'] = $key + 1;
                    if ($item->is_correct == 1) {
                        $resultDetail->forget($key);
                    }
                }
            } else {
                foreach ($resultDetail as $key => $item) {
                    $item['number'] = $key + 1;
                }
            }
        } else {
            $resultDetail = false;
        }
        $randAnswerLabels = $testResult->random_labels;
        return view('test::test.result', compact('test', 'testResult', 'resultDetail', 'resultId', 'randAnswerLabels', 'writtenDetail'));
    }
    
    /**
     * view upload images
     * @return type
     */
    public function getUploadImages() {
        Breadcrumb::add(trans('test::test.upload_image'));
        Menu::setActive('hr');
        $collectionModel = ImageModel::getTestUploadedFiles();
        return view('test::upload', compact('collectionModel'));
    }
    
    /**
     * upload images
     * @param Request $request
     * @return type
     */
    public function postUploadImages(Request $request) {
        $valid = Validator::make($request->all(), [
                   'images.*' => 'required' 
                ], [
                    'images.*.required' => trans('test::validate.no_file_selected', ['attribute' => 'File']),
                    'images.*.max' => trans('test::validate.file_max_size', ['attribute' => 'File', 'max' => 5])
                ]);
        $returnUrl = $request->get('return_url');
        if ($valid->fails()) {
            return response()->json(view('messages.errors', ['errors' => $valid->errors()])->render(), 422);
        }
        
        $images = $request->file('images');
        $requestType = $request->get('mimetype');
        if ($requestType == 'image') {
            $matchMime = 'image\/(jpeg|png|jpg|gif)';
            $typeAttrs = 'jpeg,jpg,png,gif';
        } elseif ($requestType == 'audio') {
            $matchMime = 'audio\/(mp3|wma|wav)';
            $typeAttrs = 'mp3,wav,wma';
        } else {
            $matchMime = '(image\/(jpeg|png|jpg|gif)|audio\/(mp3|wma|wav))';
            $typeAttrs = 'jpeg,jpg,png,gif,mp3,wav,wma';
        }
        $results = [];
        $upload_dir = 'tests/';
        DB::beginTransaction();
        try {
            if (!Storage::disk('public')->exists($upload_dir . 'full')) {
                Storage::disk('public')->makeDirectory($upload_dir . 'full', 0777);
                @chmod(storage_path('app/public/' . $upload_dir . 'full'), 0777);
            }
            $timeNow = time();
            foreach ($images as $key => $file) {
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getClientMimeType();
                //check mimetype valid
                if (!preg_match('/'. $matchMime .'$/i', $mimeType)) {
                    //delete image uploaded
                    if ($results) {
                        foreach ($results as $image) {
                            $image->deleteImage('tests/');
                        }
                    }
                    return response()->json(
                        trans('test::validate.file_mimes', [
                            'attribute' => 'File',
                            'types' => $typeAttrs
                        ]),
                        422
                    );
                }
                CoreView::removeExifImage($file->getRealPath());
                $randName = $timeNow . str_random(16) . '.' . $extension;
                $filePath = $upload_dir . 'full/' . $randName;
                Storage::disk('public')->put($filePath, file_get_contents($file->getRealPath()));
                $imageModel = ImageModel::create([
                    'title' => $file->getClientOriginalName(),
                    'url' => $randName,
                    'type' => 'test_image',
                    'mimetype' => $mimeType,
                    'employee_id' => auth()->id(),
                    'is_temp' => 0
                ]);
                array_push($results, $imageModel);
                $timeNow++;
            }
            DB::commit();
            if ($returnUrl) {
                $imgUrls = [];
                if ($results) {
                    foreach ($results as $imgModel) {
                        $imgUrls[] = asset($imgModel->getSrc('full', 'tests/'));
                    }
                }
                return $imgUrls;
            }
            return response()->json(trans('test::test.update_success'));
        } catch (\Exception $ex) {
            if ($results) {
                foreach ($results as $image) {
                    $image->deleteImage('tests/');
                }
            }
            DB::rollback();
            return response()->json(trans('test::validate.na_error'), 500);
        }
    }

    /**
     * check image url in use question
     * @param type $id
     */
    public function checkImageInUse(Request $request) {
        $valid = Validator::make($request->all(), [
            'image_ids' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(null, 422);
        }
        $images = ImageModel::whereIn('id', $request->get('image_ids'))->get();
        if ($images->isEmpty()) {
            return response()->json(null, 422);
        }
        $results = [];
        foreach ($images as $image) {
            $imageSrc = $image->getSrc('full', 'tests/');
            if ($imageSrc) {
                $questions = Question::with('tests')
                        ->where('is_temp', 0)
                        ->where('content', 'like', '%'. $imageSrc .'%')
                        ->get();
                $inUse = false;
                $testName = '';
                if (!$questions->isEmpty()) {
                    $inUse = true;
                    $testName = trans('test::validate.this_image_has_in_test') . ': ';
                    $exitsTest = [];
                    foreach ($questions as $question) {
                        $test = $question->tests->first();
                        if ($test && !in_array($test->id, $exitsTest)) {
                            $testName .= $test->name . ', ';
                            array_push($exitsTest, $test->id);
                        }
                    }
                    $testName = trim($testName, ', '). '. '; 
                }
                array_push($results, ['id' => $image->id, 'in_use' => $inUse, 'test_name' => $testName]);
            }
        }
        return $results;
    }
    
    /**
     * delete image
     * @param type $id
     * @return type
     */
    public function deleteImage($id) {
        $image = ImageModel::find($id);
        if ($image) {
            $image->deleteImage('tests/');
        }
        return redirect()->back()->with('messages', ['success' => [trans('test::test.delete_successful')]]);
    }
    
    /**
     * multiple action
     * @param Request $request
     * @return type
     */
    public function multiActions(Request $request) {
        if (!$request->has('action')) {
            Session::flash('messages', ['errors' => [trans('test::validate.na_error')]]);
            return response()->json(false, 422);
        }
        if (!$request->has('item_ids')) {
            Session::flash('messages', ['errors' => [trans('test::validate.no_item_selected')]]);
            return response()->json(false, 422);
        }
        $item_ids = $request->input('item_ids');
        $action = $request->input('action');
        if ($action == 'delete') {
            foreach ($item_ids as $id) {
                $item = ImageModel::find($id);
                if ($item) {
                    $item->deleteImage('tests/');
                    $item->delete();
                }
            }
        }
        Session::flash('messages', ['success' => [trans('test::validate.action_success')]]);
        return response()->json(true);
    }

    /*
     * save temp answer
     */
    public function saveTempAnswer(Request $request)
    {
        $testTemp = $request->get('test_temp');
        $qAnswer = $request->get('q_answer');
        if (!$testTemp || !isset($testTemp['test_id']) || !isset($testTemp['email']) || !$qAnswer) {
            return;
        }
        $testId = $testTemp['test_id'];
        $email = $testTemp['email'];
        $testTempItem = TestTemp::where('test_id', $testId)
                ->where('employee_email', $email)
                ->first();
        if (!$testTempItem) {
            return;
        }
        $strAnswers = $testTempItem->str_answers;
        $strAnswers = $strAnswers ? json_decode($strAnswers, true) : [];
        if (isset($qAnswer['ans'])) {
            $strAnswers[$qAnswer['qid']] = $qAnswer['ans'];
        } else {
            unset($strAnswers[$qAnswer['qid']]);
        }
        return DB::table(TestTemp::getTableName())
                ->where('test_id', $testId)
                ->where('employee_email', $email)
                ->update(['str_answers' => json_encode($strAnswers)]);
    }

    public function viewTestResult(Request $request) {
        $ricodeTest_id = $request->get('id');
        $ricodeTest = RicodeTest::where('id', $ricodeTest_id)->first();
        $candidate = $ricodeTest->candidate;
        return view('test::test.result_test_ricode', compact('ricodeTest', 'candidate'));
    }
}
