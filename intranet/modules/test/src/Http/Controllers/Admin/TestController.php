<?php

namespace Rikkei\Test\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Form;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Type;
use Rikkei\Test\Models\Question;
use Rikkei\Test\Models\Answer;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\TestResult;
use Rikkei\Test\Models\TestTemp;
use Rikkei\Test\Models\Category;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Test\Models\WrittenQuestion;
use Rikkei\Test\Models\WrittenQuestionAnswer;
use Rikkei\Test\View\ViewTest;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Collections\SheetCollection;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Test\View\TestValueBinder;
use Rikkei\Core\View\CookieCore;
use Validator;
use Session;
use Illuminate\Support\Facades\DB;
use URL;
use Carbon\Carbon;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Test\Models\LangGroup;
use Rikkei\Test\Models\TestQuestion;

class TestController extends Controller
{

    protected $test;

    public function __construct(Test $test)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(trans('test::test.test'), route('test::admin.test.index'));
        Menu::setActive('hr');
        $this->test = $test;
    }

    /**
     * validate test
     * @param type $data
     * @return type
     */
    public function validator($data)
    {
        $valid = Validator::make($data, [
            'name' => 'required',
            'time' => 'required|min:1',
            'lang_code' => 'required',
        ], [
            'name.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.name')]),
            'time.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.time')]),
            'time.min' => trans('test::validate.please_input_value_greater', ['min' => 1]),
            'lang_code.required' => trans('test::validate.please_input_field', ['field' => trans('test::test.Language')]),
        ]);
        return $valid;
    }

    /**
     * list tests
     * @return type
     */
    public function index()
    {
        return view('test::manage.test.index', [
            'collectionModel' => $this->test->getGridData(),
            'types' => Type::getList()
        ]);
    }

    /**
     * show test
     * @param type $id
     * @return type
     */
    public function show($id)
    {
        $item = $this->test->findItemByLang($id, Session::get('locale'), true);
        if (!$item) {
            abort(404);
        }
        if ($item->id != $id) {
            return redirect()->route('test::admin.test.show', ['id' => $item->id]);
        }
        if (!ViewTest::hasPermiss($item->created_by, 'test::admin.test.show')) {
            return CoreView::viewErrorPermission();
        }
        Breadcrumb::add(trans('test::test.view'));

        $questions = $item->questions()->with(['childs', 'answers']);
        $writtenQuestions = writtenQuestion::getCollectWrittenQuestion($item->id);
        $filterStatus = FormView::getFilterData('status');
        if ($filterStatus) {
            $questions->where('status', $filterStatus);
            $writtenQuestions->where('status', $filterStatus);
        }
        $questions = $questions->get();
        $writtenQuestions = $writtenQuestions->get();
        return view('test::manage.test.show', compact('item', 'questions', 'writtenQuestions'));
    }

    /**
     * list test results
     * @param type $test_id
     * @return type
     */
    public function listResults($testId, Request $request)
    {
        $testerType = $request->get('tester_type');
        $testerType = ($testerType && in_array($testerType, [ViewTest::TESTER_PRIVATE, ViewTest::TESTER_PUBLISH]))
            ? $testerType : ViewTest::TESTER_PRIVATE;
        $langCode = $request->get('lang');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $test = $this->test->findItemByLang($testId, $langCode, true);
        if (!$test) {
            abort(404);
        }
        if ($testId != $test->id) {
            $request->merge([
                'id' => $test->id,
            ]);
            return redirect()->route('test::admin.test.results', $request->query());
        }
        if (!ViewTest::hasPermiss($test->created_by, 'test::admin.test.results')) {
            return CoreView::viewErrorPermission();
        }
        Breadcrumb::add(trans('test::test.list_results'));
        $collectionModel = Result::listByTestId($testId, $testerType);

        $total_questions = $test->questions()
            ->where('status', ViewTest::STT_ENABLE)->count();
        $totalResult = $test->results()
            ->where('tester_type', $testerType)
            ->count();
        $questionAnalytic = collect();

        return view(
            'test::manage.test.list_result',
            compact('collectionModel', 'test', 'total_questions', 'questionAnalytic', 'totalResult', 'testerType')
        );
    }

    public function exportResults($testId, Request $request)
    {
        $testerType = $request->get('tester_type');
        $resultIds = $request->get('result_ids');
        $collection = Result::listByTestId($testId, $testerType, $resultIds, true);

        if ($collection->isEmpty()) {
            return redirect()->back()->withInput()
                ->with('messages', ['errors' => [trans('test::test.no_item')]]);
        }
        if (!$resultIds) {
            $resultIds = $collection->pluck('id')->toArray();
        }
        $totalWrittenQuestionAnswers = WrittenQuestionAnswer::countAnswerByResultIds($resultIds);
        // Get danh sách câu hỏi và trả lời phần tự luận
        if ($totalWrittenQuestionAnswers) {
            $writtenQuestions = WrittenQuestion::getCollectWrittenQuestion($testId)
                ->leftjoin('ntest_written_question_answers', 'ntest_written_question_answers.written_id', '=', 'ntest_written_questions.id')
                ->leftjoin('ntest_results', 'ntest_written_question_answers.result_id', '=', 'ntest_results.id')
                ->whereIn('ntest_results.id', $resultIds)
                ->select('ntest_written_questions.id as question_id', 'ntest_results.id as result_id', 'answer', 'ntest_written_questions.content as written_question')
                ->get();
        }
        // Gán câu hỏi và trả lời phần tự luận vào collection
        if (isset($writtenQuestions) && $writtenQuestions) {
            foreach ($collection as $order => $item) {
                foreach ($writtenQuestions as $k => $question) {
                    if ($question->result_id == $item->id) {
                        $answers[] = trim($question->written_question);
                        $itemAnswer = trim($question->answer);
                        if (strlen($itemAnswer) > 0) {
                            $firstChar = substr($itemAnswer, 0, 1);
                            if ($firstChar == '=') {
                                $itemAnswer = "'".$itemAnswer;
                            }
                        }
                        $answers[] = $itemAnswer;
                        $answers['result_id'] = $question->result_id;
                        unset($writtenQuestions[$k]);
                    }
                }
                if (isset($answers['result_id']) && $answers['result_id'] == $collection[$order]->id) {
                    unset($answers['result_id']);
                    $collection[$order]->written = $answers;
                    $answers = [];
                }
            }
        }

        $test = Test::findOrFail($testId);
        $countQuestion = $test->questions->count();
        $fileName = Carbon::now()->format('Ymd') . '_' . str_slug($test->name) . '_result';
        //create excel file
        Excel::create($fileName, function ($excel) use ($test, $collection, $countQuestion, $totalWrittenQuestionAnswers) {
            $excel->setTitle($test->name);
            $excel->sheet('result', function ($sheet) use ($test, $collection, $countQuestion, $totalWrittenQuestionAnswers) {
                $count = isset($totalWrittenQuestionAnswers) ? max($totalWrittenQuestionAnswers->toArray()) : 0;
                $row = [];
                if ((int)$count > 0) {
                    for ($i = 1; $i <= $count; $i++) {
                        $row[] = trans('test::test.written question :question', ['question' => $i]);
                        $row[] = trans('test::test.written answer :answer', ['answer' => $i]);
                    }
                }
                $sheet->mergeCells('A1:F1');
                //set test name at merge cell
                $sheet->row(1, ['Test: ' . $test->name]);
                //set row header
                $rowHeader = [trans('core::view.NO.'), trans('test::test.full_name'), trans('test::test.email'), 'Division', trans('test::test.total_corrects'), trans('test::test.total_answers'), trans('test::test.total written question answers'), trans('test::test.time')];
                $sheet->row(2, array_merge($rowHeader, $row));
                //format data type column
                $sheet->setColumnFormat(array(
                    'B' => '@',
                    'C' => '@',
                    'D' => '@',
                    'E' => '0',
                    'F' => '0',
                    'G' => '0',
                    'H' => '@',
                ));
                //set data
                foreach ($collection as $order => $item) {
                    $rowData = [
                        $order + 1,
                        $item->employee_name,
                        $item->employee_email,
                        $item->team_names,
                        $item->total_corrects,
                        ($item->total_question !== null) ? $item->total_question : $countQuestion,
                        isset($totalWrittenQuestionAnswers['count_' . $item->id]) ? $totalWrittenQuestionAnswers['count_' . $item->id] : null,
                        $item->created_at
                    ];
                    if (isset($item->written)) {
                        foreach ($item->written as $written) {
                            $rowData[] = strip_tags(html_entity_decode($written));
                        }
                    }
                    $sheet->row($order + 3, $rowData);
                }
                //set customize style
                $sheet->getStyle('A2:G2')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'size' => 18,
                        'bold' => true
                    ]
                ]);
                //set wrap text
                $sheet->getStyle('A2:G100')->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    /**
     * import test by excel file
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function importTest(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'file' => 'required|max:8192'
        ]);
        if ($valid->fails()) {
            return response()->json($valid->errors()->first('file'), 422);
        }
        //check extension file
        $file = $request->file('file');
        $testId = $request->get('test_id');
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json(trans('validation.mimes', ['attribute' => 'import file', 'values' => 'xlsx, xls, csv']), 422);
        }
        $langCode = $request->get('lang');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }

        //set time limit max to 180 seconds
        set_time_limit(180);

        try {
            $valueBinder = new TestValueBinder;
            $data = Excel::setValueBinder($valueBinder)->load($file->getRealPath(), function ($reader) {
                $reader->formatDates(false);
                $reader->calculate(false);
            });
            $dataSheets = $data->getAllSheets();
            $data = $data->get();
        } catch (\Exception $ex) {
            return response()->json(trans('test::test.file_format_error_or_not_read'), 422);
        }

        $type1 = null;
        $type2 = null;
        $type4 = null;
        $sheetType1 = null;
        $sheetType2 = null;
        $sheetType4 = null;
        if ($data instanceof SheetCollection) {
            foreach ($data as $index => $sheet) {
                $sheetTitle = mb_strtolower($sheet->getTitle(), 'UTF-8');
                if (in_array($sheetTitle, ViewTest::ARR_TYPE_1)) {
                    $type1 = $sheet;
                    $sheetType1 = $dataSheets[$index];
                }
                if (in_array($sheetTitle, ViewTest::ARR_TYPE_2)) {
                    $type2 = $sheet;
                    $sheetType2 = $dataSheets[$index];
                }
                if (in_array($sheetTitle, ViewTest::ARR_TYPE_4)) {
                    $type4 = $sheet;
                }
            }
        } else {
            $sheetTitle = mb_strtolower($data->getTitle(), 'UTF-8');
            $type1 = $data;
            $sheetType1 = $dataSheets;
            if (in_array($sheetTitle, ViewTest::ARR_TYPE_2)) {
                $type2 = $data;
                $sheetType2 = $dataSheets;
            }
            if (in_array($sheetTitle, ViewTest::ARR_TYPE_4)) {
                $type4 = $data;
            }
        }
        //upload temp image in sheets
        $dirName = ViewTest::uploadTempImageBySheets(['type1' => $sheetType1, 'type2' => $sheetType2]);

        //collect category;
        $collectCats = [];
        foreach (array_keys(ViewTest::ARR_CATS) as $key) {
            $collectCats[$key] = [];
        }

        DB::beginTransaction();
        try {
            $results = [];
            $resultsHtml = '';
            $resultsWrittenHtml = '';

            // type 1
            if ($type1 && !$type1->isEmpty()) {
                foreach ($type1 as $rowIndex => $row) {
                    $type = mb_strtolower($row->type, 'UTF-8');
                    $correct = trim($row->correct, " ,\t\n\r\x0B");
                    $explain = trim($row->explain, " ,\t\n\r\x0B");
                    $multi_choice_raw = trim($row->multi_choice);
                    if ($multi_choice_raw != 1) {
                        $multi_choice = 0;
                    } else {
                        $multi_choice = 1;
                    }
                    $disable_row = trim($row->disable, " ,\t\n\r\x0B");
                    $status = ViewTest::STT_ENABLE;
                    if ($disable_row) {
                        $status = ViewTest::STT_DISABLE;
                    }

                    $answers = [];
                    $qContent = htmlspecialchars(trim($row->content));
                    preg_match_all('/\`\`\`image\s*(.*)\s*\`\`\`/si', $qContent, $matchs);
                    $imageUrl = isset($matchs[1]) ? $matchs[1] : [];

                    //check if both content and image url are null
                    if ($qContent == null && $imageUrl == null) {
                        continue;
                    }
                    if ($type == null || in_array($type, ViewTest::ARR_TYPE_2)) {
                        continue;
                    }
                    //check if none correct answer
                    if (!$correct) {
                        throw new \Exception(trans('test::test.correct_answer_not_empty') . ', sheet "' . $type1->getTitle() . '", row "' . ($rowIndex + 2) . '"', 422);
                    }
                    //set question content
                    if ($imageUrl) {
                        //check image_url valid
                        foreach ($imageUrl as $url) {
                            ViewTest::checkValidSource($url, $type1->getTitle(), $rowIndex);
                        }
                    }
                    if (in_array($type, ViewTest::ARR_TYPE_1)) {
                        $corrects = explode(',', $correct);
                        if ($corrects) {
                            foreach ($corrects as $text) {
                                $ans = Answer::create(['content' => ViewTest::codeFormat(htmlspecialchars(trim($text))), 'is_temp' => 1]);
                                array_push($answers, ['id' => $ans->id, 'correct' => true]);
                            }
                        } else {
                            throw new \Exception(trans('test::test.list_answers_empty') . ', sheet "' . $type1->getTitle() . '", row "' . ($rowIndex + 2) . '"', 422);
                        }
                    } else {
                        $correct = mb_strtoupper($correct, 'UTF-8');
                        $corrects = array_map('trim', explode(',', $correct));
                        $ans_labels = [
                            'A' => trim(ViewTest::convertBool($row->a)),
                            'B' => trim(ViewTest::convertBool($row->b)),
                            'C' => trim(ViewTest::convertBool($row->c)),
                            'D' => trim(ViewTest::convertBool($row->d)),
                            'E' => trim(ViewTest::convertBool($row->e)),
                            'F' => trim(ViewTest::convertBool($row->f)),
                            'G' => trim(ViewTest::convertBool($row->g)),
                            'H' => trim(ViewTest::convertBool($row->h))
                        ];

                        // list answers label of question
                        $answer_lables = [];
                        $count_answer = 0;
                        foreach ($ans_labels as $lb => $ans_lb) {
                            if ($ans_lb != null) {
                                $count_answer++;
                                $answer_lables[$lb] = [$ans_lb, in_array($lb, $corrects)];
                            }
                        }
                        //check if null answer
                        if ($count_answer < 2) {
                            throw new \Exception(trans('test::test.list_answers_least_than') . ', sheet "' . $type1->getTitle() . '", row "' . ($rowIndex + 2) . '"', 422);
                        }
                        // insert answers
                        foreach ($ans_labels as $lb => $ans_lb) {
                            if ($ans_lb != null) {
                                $label = $answer_lables[$lb];
                                $answer_item = Answer::create(['label' => $lb, 'content' => ViewTest::codeFormat(htmlspecialchars($label[0])), 'is_temp' => 1]);
                                $answers[$lb] = ['id' => $answer_item->id, 'correct' => $label[1]];
                            }
                        }
                        //sort by label list answer
                        ksort($answers);
                    }
                    $explain = htmlspecialchars($explain);
                    //replace code format
                    $rowOrder = 'type1_' . $row->stt;
                    $qContent = ViewTest::codeFormat($qContent) . ViewTest::getTempImageUrl($rowOrder, $dirName);
                    $explain = ViewTest::codeFormat($explain);
                    $question = Question::create([
                        'row_order' => $rowOrder,
                        'content' => $qContent,
                        'image_urls' => $imageUrl ? $imageUrl : null,
                        'explain' => $explain,
                        'type' => $type,
                        'is_temp' => 1,
                        'multi_choice' => $multi_choice,
                        'status' => $status
                    ]);
                    if ($answers) {
                        foreach ($answers as $answer) {
                            $question->answers()->attach($answer['id'], ['is_correct' => $answer['correct']]);
                        }
                    }
                    $question->answers = $question->answers()->orderBy('label', 'asc')->get();
                    //collect category
                    $listCats = Category::addAndCollect($row, $question->id);
                    ViewTest::collectCategory($listCats, $collectCats);
                    //push result
                    array_push($results, $question);
                    $resultsHtml .= view('test::manage.includes.tr-question-item', [
                        'qItem' => $question,
                        'order' => count($results) - 1,
                        'cats' => $listCats,
                        'currentLang' => $langCode,
                    ])->render();
                }
            }

            // type 2, sheet 2
            if ($type2 && !$type2->isEmpty()) {
                $type2Group = [];
                foreach ($type2 as $idx => $item) {
                    if ($item->id !== null) {
                        $item->row_index = $idx + 1;
                        $type2Group[$item->id][] = $item;
                    }
                }

                foreach ($type2Group as $row2Id => $arrItems) {
                    if ($arrItems) {

                        $parentContent = '';
                        $collectContent = '';
                        $parent_image_urls = [];
                        $parent_explain = '';
                        $parent_status = ViewTest::STT_ENABLE;
                        $question_ids = [];
                        $answerArray = [];

                        //insert and collect answer
                        $listAnswerLabels = collect($arrItems)->groupBy('correct');
                        if ($listAnswerLabels->isEmpty()) {
                            throw new \Exception(trans('test::test.list_answers_empty') . ', sheet "' . $type2->getTitle() . '", question ID "' . $row2Id . '"', 422);
                        }
                        //collect answer
                        $collectAns = [];
                        foreach ($listAnswerLabels as $keyLb => $arrAns) {
                            if (!$keyLb) {
                                continue;
                            }
                            $firstAns = null;
                            foreach ($arrAns as $ansContent) {
                                if ($ansContent->answer_content) {
                                    $firstAns = $ansContent;
                                    break;
                                }
                            }
                            if (!$firstAns) {
                                throw new \Exception(trans('test::test.correct_answer_not_in_list') . ', sheet "' . $type2->getTitle() . '", question ID "' . $row2Id . '"', 422);
                            }
                            $answer = Answer::create([
                                'label' => mb_strtoupper($firstAns->correct, 'UTF-8'),
                                'content' => ViewTest::codeFormat(htmlspecialchars(trim($firstAns->answer_content))),
                                'is_temp' => 1
                            ]);
                            $collectAns[$answer->label] = $answer;
                            $answerArray[$answer->id] = ['is_correct' => 0];
                        }
                        //insert children question
                        foreach ($arrItems as $cidx => $row) {
                            $content = htmlspecialchars(trim($row->content));
                            $explain = trim($row->explain);
                            //preg match get all image url
                            preg_match_all('/\`\`\`image\s*(.*?)\s*\`\`\`/si', $content, $matchs);
                            $imageUrl = isset($matchs[1]) ? $matchs[1] : [];
                            $answerLabel = mb_strtoupper($row->correct, 'UTF-8');
                            $answerContent = trim($row->answer_content);
                            $disable_row = trim($row->disable, " ,\t\n\r\x0B");
                            $i = $row->row_index;

                            //check if row null, ignored image_url column
                            if ($content == null && $answerLabel == null && $answerContent == null) {
                                continue;
                            }
                            //check if only have content
                            if ($content != null && $answerLabel == null && $answerContent == null) {
                                $parentContent .= ViewTest::codeFormat($content);
                                $collectContent .= $parentContent;
                                continue;
                            }
                            //check if null answer label
                            if ($answerLabel == null) {
                                throw new \Exception(trans('test::test.list_answers_empty') . ', sheet "' . $type2->getTitle() . '", row "' . ($i == 0 ? ($i + 2) : ($i + 1)) . '"', 422);
                            }

                            $explain = ViewTest::codeFormat(htmlspecialchars($explain));
                            $content = ViewTest::codeFormat($content);
                            $status = ViewTest::STT_ENABLE;
                            if ($disable_row) {
                                $status = ViewTest::STT_DISABLE;
                            }
                            if ($cidx == 0) {
                                $parent_status = $status;
                            }

                            //if not null content, ignored image url then create child question
                            if ($content != null) {
                                if ($explain != null) {
                                    $parent_explain .= '<p>' . $explain . '</p>';
                                }

                                //check image_url valid
                                if ($imageUrl) {
                                    foreach ($imageUrl as $url) {
                                        ViewTest::checkValidSource($url, $type2->getTitle(), $i);
                                        $parent_image_urls[] = $url;
                                    }
                                }

                                //create child question
                                $question = Question::create([
                                    'content' => $content,
                                    'image_urls' => $imageUrl ? $imageUrl : null,
                                    'explain' => $explain,
                                    'type' => ViewTest::ARR_TYPE_2[0],
                                    'is_temp' => 1,
                                    'status' => $status
                                ]);

                                //$parentContent .= ' ' . $content;
                                $collectContent .= ' ' . $content;
                                if (isset($collectAns[$answerLabel])) {
                                    $answer = $collectAns[$answerLabel];
                                    $question->answers()->attach($answer->id, ['is_correct' => 1]);
                                }

                                //collect child question
                                $question_ids[] = $question->id;
                            }
                        }

                        $rowOrder = 'type2_' . $row2Id;
                        $tempImgUrl = ViewTest::getTempImageUrl($rowOrder, $dirName);
                        $parentContent .= $tempImgUrl;
                        $collectContent .= $tempImgUrl;
                        $parentQuestion = Question::create([
                            'row_order' => $rowOrder,
                            'content' => $parentContent,
                            'image_urls' => $parent_image_urls,
                            'explain' => $parent_explain,
                            'type' => ViewTest::ARR_TYPE_2[0],
                            'is_temp' => 1,
                            'status' => $parent_status
                        ]);
                        $parentQuestion->answers()->attach($answerArray);

                        Question::whereIn('id', $question_ids)
                            ->update(['parent_id' => $parentQuestion->id]);

                        //collect category
                        $listCats = Category::addAndCollect($arrItems[0], $parentQuestion->id);
                        ViewTest::collectCategory($arrItems[0], $collectCats);

                        array_push($results, $parentQuestion);
                        //$parentQuestion->content = $collectContent;
                        $resultsHtml .= view('test::manage.includes.tr-question-item', [
                            'qItem' => $parentQuestion,
                            'order' => count($results) - 1,
                            'cats' => $listCats,
                            'currentLang' => $langCode,
                        ])->render();
                    }
                }
            }

            //type4, sheet 3
            if ($type4 && !$type4->isEmpty() && $testId) {
                foreach ($type4 as $rowIndex => $row) {
                    $content = $row->content;
                    $status = isset($row->disable) && $row->disable == ViewTest::STT_ENABLE ? ViewTest::STT_DISABLE : ViewTest::STT_ENABLE;
                    if ($content) {
                        $checkExists = WrittenQuestion::where('content', $content)
                            ->where('test_id', $testId)
                            ->count();
                        if (!$checkExists) {
                            $writtenQuestions = WrittenQuestion::create([
                                'content' => $content,
                                'status' => $status,
                                'test_id' => $testId,
                            ]);
                            if ($row->category) {
                                $data['name'] = $row->category;
                                $data['type_cat'] = ViewTest::ARR_TYPE_4[1];
                                $category = Category::addIfNotExists($data, null, $testId);
                                DB::table('ntest_written_category')->insert(['written_id' => $writtenQuestions->id, 'cat_id' => $category->id]);
                            }
                            array_push($results, $writtenQuestions);
                            $resultsWrittenHtml .= view('test::manage.includes.tr-written-item', [
                                'qItem' => $writtenQuestions,
                                'order' => 1,
                                'testId' => $testId,
                                'currentLang' => $langCode,
                            ])->render();
                        }
                    }
                }
            }
            DB::commit();
            $totalQuestion = count($results);
            return [
                'html' => $resultsHtml,
                'writtenHtml' => $resultsWrittenHtml,
                'message' => $totalQuestion ? trans('test::test.number_imported_question') . ': ' . $totalQuestion
                    : trans('test::test.there_are_no_valid_question'),
                'categories' => $collectCats
            ];
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            $message = trans('core::message.Error system, please try later!');
            if ($ex->getCode() == 422) {
                $message = $ex->getMessage();
            }
            return response()->json($message, 422);
        }
    }

    /**
     * create test
     * @return type
     */
    public function create(Request $request)
    {
        Breadcrumb::add(trans('test::test.add_new'));
        $groupTypes = Test::groupTypesLabel();
        $types = Type::getList();
        $item = null;
        $testLangs = null;
        $testLangClone = null;
        $testLangId = $request->get('test_lang_id');
        $collectCats = null;
        if ($testLangId) {
            $testLangs = LangGroup::listTestsByLangGroup($testLangId);
            $testLangClone = Test::find($testLangId);
            //collect cats
            if ($testLangClone) {
                $testLangClone->created_by = null;
                $testLangClone->id = null;
                $collectCats = $testLangClone->getQuestionCats();
                if ($collectCats && !$collectCats->isEmpty()) {
                    $collectCats = $collectCats->groupBy('type_cat');
                }
            }
        }
        return view('test::manage.test.create', compact(
            'groupTypes',
            'types',
            'item',
            'testLangs',
            'testLangClone',
            'collectCats'
        ));
    }

    /**
     * insert test
     * @param Request $request
     * @return type
     */
    public function save(Request $request)
    {
        $data = $request->all();
        $valid = $this->validator($data);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        if (!isset($data['display_option'])){
            $data['display_option'] = null;
        }
        if (!isset($data['written_cat'])){
            $data['written_cat'] = null;
        }
        if (!isset($data['total_written_question'])){
            $data['total_written_question'] = null;
        }
        $testId = $request->get('id');
        $test = null;
        if ($testId) {
            $test = $this->test->find($testId);
            if (!$test) {
                abort(404);
            }
            if (!ViewTest::hasPermiss($test->created_by, 'test::admin.test.update')) {
                return CoreView::viewErrorPermission();
            }
        }
        $thumbnailPath = Test::uploadThumbnail(Input::file('thumbnail'), $test);
        $newThumbnail = !empty($thumbnailPath) ? $thumbnailPath['new_thumbnail'] : null;
        $oldThumbnail = !empty($thumbnailPath) ? $thumbnailPath['old_thumbnail'] : null;

        DB::beginTransaction();
        try {
            $name = $data['name'];
            $data['slug'] = str_slug($name);
            if (!$testId) {
                $data['url_code'] = Test::genCode();
                $data['is_temp'] = 0;
                if (!isset($data['created_by']) || !$data['created_by']) {
                    $data['created_by'] = auth()->id();
                }
            } else {
                $showAnswerOptions = [Test::SHOW_RESULT_ONLY, Test::SHOW_WRONG_ANSWER, Test::SHOW_ALL_ANSWER];
                $data['show_detail_answer'] = isset($data['show_detail_answer']) && in_array($data['show_detail_answer'], $showAnswerOptions)
                    ? $data['show_detail_answer'] : Test::SHOW_RESULT_ONLY;
            }
            $data['is_auth'] = !isset($data['is_auth']);
            $data['is_lunar'] = isset($data['is_lunar']);
            $data['random_answer'] = isset($data['random_answer']);
            $data['random_order'] = isset($data['random_order']);
            $data['limit_question'] = isset($data['limit_question']);
            $data['set_valid_time'] = isset($data['set_valid_time']);
            $data['valid_view_time'] = isset($data['valid_view_time']);
            $data['set_min_point'] = isset($data['set_min_point']);
            $data['min_point'] = (isset($data['set_min_point']) && $data['set_min_point'] == Test::SET_MIN_POINT) ? $data['min_point'] : null;
            //set type
            $typeId = $request->get('type_id');
            $data['type_id'] = $typeId ? $typeId : null;

            if ($newThumbnail) {
                $data['thumbnail'] = $newThumbnail;
            }

            $qItemIds = $request->get('q_items');
            $syncQuestionIds = [];
            $collectCats = [];
            if ($qItemIds && count($qItemIds) > 0) {
                $qItems = Question::with(['answers', 'childs'])
                    ->whereIn('id', $qItemIds)
                    ->get();
                $dataAnswerIds = [];
                $dataChildIds = [];
                if (!$qItems->isEmpty()) {
                    $countQItems = count($qItemIds);
                    foreach ($qItems as $qItem) {
                        $order = array_search($qItem->id, $qItemIds);
                        if ($order === false) {
                            $order = $countQItems;
                            $countQItems++;
                        }
                        $syncQuestionIds[$qItem->id] = [
                            'question_id' => $qItem->id,
                            'order' => $order,
                        ];
                        $dataAnswerIds = array_merge($dataAnswerIds, $qItem->answers->pluck('id')->toArray());
                        $dataChildIds = array_merge($dataChildIds, $qItem->childs->pluck('id')->toArray());
                        //categories
                        $categories = $qItem->categories();
                        $categories->update(['is_temp' => 0]);
                        Test::collectCats($collectCats, $categories->get());
                    }
                }
                if ($syncQuestionIds) {
                    $qNotTempIds = array_keys($syncQuestionIds);
                    if ($dataChildIds) {
                        $qNotTempIds = array_merge($qNotTempIds, $dataChildIds);
                    }
                    Question::whereIn('id', $qNotTempIds)
                        ->update(['is_temp' => 0]);
                }
                if ($dataAnswerIds) {
                    Answer::whereIn('id', $dataAnswerIds)
                        ->update(['is_temp' => 0]);
                }

                $data['question_cat_ids'] = $collectCats;
            }

            //create and update test
            if (!$testId) {
                $test = Test::create($data);
            } else {
                $test->update($data);
            }

            //set test group
            $groupTestIds = $request->get('group_ids');
            $groupId = $request->get('group_id');
            $langCode = $data['lang_code'];
            $groupTestIds[$langCode] = $test->id;
            if (!$groupId && $testId && ($langGroup = $test->langGroup)) {
                $groupId = $langGroup->group_id;
            }
            LangGroup::updateTestIds($groupTestIds, $groupId);

            //sync option by group id
            if ($groupId) {
                $test->syncOptionByGroupId($groupId, $groupTestIds);
            }
            //update sync question order
            /*if ($testId && $qItemIds && count($qItemIds) > 0) {
                $changeQuesOrder = $request->get('change_q_order');
                $test->syncLangQuestionsOrder($qItemIds, $changeQuesOrder, $groupId, $groupTestIds);
            }*/
            //sync current question
            if ($testId) {
                TestQuestion::where('test_id', $testId)->delete();
            }
            if ($syncQuestionIds) {
                $syncQuestionIds = array_map(function ($sItem) use ($test) {
                    $sItem['test_id'] = $test->id;
                    return $sItem;
                }, $syncQuestionIds);
                TestQuestion::insert($syncQuestionIds);
            }

            if ($testId) {
                $groupId = LangGroup::where('test_id', $testId)->pluck('group_id')->first();
                //delete question, answer not attach
                Question::delNotAttach();
                Answer::delNotAttach();
                //forget question random index
                Session::forget('question_index_' . $groupId);
                Session::forget('writtenQuestions_' . $groupId);
                // delete old thumbnail
                Test::deleteThumbnail($oldThumbnail);
            }
            // delete temp questions, answers
            Answer::where('is_temp', 1)->delete();
            Question::where('is_temp', 1)->delete();
            Category::where('is_temp', 1)
                ->whereNotIn('type_cat', ViewTest::ARR_TYPE_4)
                ->delete();

            DB::commit();
            return redirect()->route('test::admin.test.edit', ['id' => $test->id, 'lang' => $langCode])
                ->with('messages', ['success' => [trans('test::test.save_data_success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            // delete new thumbnail
            Test::deleteThumbnail($newThumbnail);
            $message = $ex->getMessage();
            if (config('app.env') == 'production') {
                $message = trans('core::message.Error system, please try later!');
            }
            return redirect()->back()
                ->withInput()
                ->with('messages', ['errors' => [$message]]);
        }
    }

    /**
     * Edit test
     * @param type $id
     * @return type
     */
    public function edit($id, Request $request)
    {
        $currentLang = $request->get('lang');
        if (!$currentLang) {
            $currentLang = Session::get('locale');
        }
        $item = $this->test->findItemByLang($id, $currentLang, true);
        if (!$item) {
            abort(404);
        }
        if ($item->id != $id) {
            $rqParams = [
                'id' => $item->id,
                'lang' => $currentLang,
            ];
            return redirect()->route('test::admin.test.edit', $rqParams);
        }

        if (!ViewTest::hasPermiss($item->created_by, 'test::admin.test.edit')) {
            return CoreView::viewErrorPermission();
        }
        $checkTesting = $request->get('ignore_testing');
        $testing = $item->checkTesting();
        if (!$checkTesting && $testing) {
            return view('test::manage.test.confirm-edit', ['testId' => $id]);
        }
        $types = Type::getList();
        $questions = $item->questions()
            ->with(['childs'])
            ->get();
        $collectCats = $item->getQuestionCats();
        if ($collectCats && !$collectCats->isEmpty()) {
            $collectCats = $collectCats->groupBy('type_cat');
        }
        $totalQuestion = count($questions);
        $testLangs = LangGroup::listTestsByLangGroup($id);
        $testLangIds = $testLangs->pluck('id')->toArray();
        $itemKey = array_search($item->id, $testLangIds);
        if ($itemKey !== false) {
            unset($testLangIds[$itemKey]);
        }
        $notEqualQuestions = !TestQuestion::checkEqualQuestions($testLangIds, $totalQuestion);
        $writtenQuestion = WrittenQuestion::getCollectWrittenQuestion($id)->get();

        return view('test::manage.test.edit', compact(
            'item',
            'types',
            'questions',
            'writtenQuestion',
            'collectCats',
            'totalQuestion',
            'testLangs',
            'notEqualQuestions'
        ));
    }

    public function searchAjax(Request $request)
    {
        return Test::searchAjax($request->get('q'), $request->except('q'));
    }

    /**
     * delete item
     * @param type $id
     * @return type
     */
    public function destroy($id)
    {
        $test = $this->test->find($id);
        if (!$test) {
            abort(404);
        }
        if (!ViewTest::hasPermiss($test->created_by, 'test::admin.test.destroy')) {
            return CoreView::viewErrorPermission();
        }
        $questions = $test->questions;
        $writtenQuestions = WrittenQuestion::getCollectWrittenQuestion($id)->pluck('id')->toArray();
        DB::beginTransaction();
        try {
            WrittenQuestionAnswer::whereIn('written_id', $writtenQuestions)->delete();
            WrittenQuestion::where('test_id', $id)->delete();
            if (!$questions->isEmpty()) {
                foreach ($questions as $question) {
                    $question->answers()->delete();
                    $question->delete();
                }
            }
            $test->delete();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            // delete new thumbnail
            $message = $ex->getMessage();
            if (config('app.env') == 'production') {
                $message = trans('core::message.Error system, please try later!');
            }
            return redirect()->back()
                ->withInput()
                ->with('messages', ['errors' => [$message]]);
        }

        return redirect()->back()->with('messages', ['success' => [trans('test::validate.delete_success')]]);
    }

    /**
     * remove results
     * @param type $id
     * @return type
     */
    public function removeResult($id)
    {
        $result = Result::find($id);
        if (!$result) {
            abort(404);
        }
        $test = Test::find($result->test_id);
        if (!ViewTest::hasPermiss($test->created_by, 'test::admin.test.remove_result')) {
            return CoreView::viewErrorPermission();
        }
        WrittenQuestionAnswer::where('result_id', $id)->delete();
        $result->details()->delete();
        TestTemp::where('test_id', $result->test_id)
            ->where('employee_email', $result->employee_email)
            ->delete();
        $result->delete();
        return redirect()->back()->with('messages', ['success' => [trans('test::validate.delete_success')]]);
    }

    /**
     * action with multiple items
     * @param Request $request
     * @return type
     */
    public function mAction(Request $request)
    {
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
                $test = $this->test->find($id);
                if ($test) {
                    $questions = $test->questions;
                    if (!$questions->isEmpty()) {
                        foreach ($questions as $question) {
                            $question->answers()->delete();
                            $question->delete();
                        }
                    }
                    $test->delete();
                }
            }
        }
        Session::flash('messages', ['success' => [trans('test::validate.action_success')]]);
        return response()->json(true);
    }

    /**
     * reset random data of current user
     * @param Request $request
     * @return type
     */
    public function resetTestingRandom(Request $request)
    {
        $testIds = $request->get('test_ids');
        if (!$testIds) {
            return redirect()->back()->with('messages', ['errors' => [trans('test::test.no_item_selected')]]);
        }
        foreach ($testIds as $id) {
            //clear random question
            $keyRandQuestion = md5('question_index_' . $id);
            CookieCore::forget($keyRandQuestion);
            //clear random answer
            $keyRandAnswer = md5('question_answers_labels_' . $id . '_' . Session::getId());
            Session::forget($keyRandAnswer);
        }
        return redirect()->back()->with('messages', ['success' => [trans('test::test.reset_successfull')]]);
    }

    /**
     * remove multiple result
     * @param Request $request
     * @return type
     */
    public function removeMultiResult(Request $request)
    {
        if (!$request->has('item_ids')) {
            Session::flash('messages', ['errors' => [trans('test::validate.no_item_selected')]]);
            return response()->json(false, 422);
        }
        $itemIds = $request->input('item_ids');
        DB::beginTransaction();
        try {
            foreach ($itemIds as $id) {
                $result = Result::find($id);
                if (!$result) {
                    continue;
                }
                $result->details()->delete();
                TestTemp::where('test_id', $result->test_id)
                    ->where('employee_email', $result->employee_email)
                    ->delete();
                $result->delete();
            }
            DB::commit();
            Session::flash('messages', ['success' => [trans('test::validate.action_success')]]);
            return response()->json(true);
        } catch (\Exception $ex) {
            DB::rollback();
            Session::flash('messages', ['errors' => [trans('test::validate.na_error')]]);
            return response()->json(false, 422);
        }
    }

    public function getMoreResult(Request $request)
    {
        return Result::getListMoreResultByEmail($request->test_result_id);
    }

    public function ajaxGetWrittenCat()
    {
        $id = Input::get('test_id');
        $cats = WrittenQuestion::listWrittenCatByTestID($id)->toArray();

        return response()->json(['response' => $cats]);
    }

    /**
     * Ajax generate analytics view
     * @return string
     * @throws \Throwable
     */
    public function AjaxGetAnalytics()
    {
        $testId = Input::get('test_id');
        $testerType = Input::get('testerType');
        $status = Input::get('status');
        $questionAnalytic = TestResult::questionAnalytic($testId, ['tester_type' => $testerType], $status);
        $test = $this->test->findItemByLang($testId, Session::get('locale'), true);
        $totalResult = $test->results()
            ->where('tester_type', $testerType)
            ->count();

        return view('test::manage.test.analytics', [
            'questionAnalytic' => $questionAnalytic,
            'totalResult' => $totalResult,
            'testerType' => $testerType,
            'test' => $test,
            'status' => $status
        ])->render();
    }
}
