<?php

namespace Rikkei\Test\Http\Controllers\Admin;

use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\CoreLang;
use Rikkei\Test\Models\Answer;
use Rikkei\Test\Models\Category;
use Rikkei\Test\Models\Question;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\TestQuestion;
use Rikkei\Test\Models\WrittenQuestion;
use Rikkei\Test\Models\WrittenQuestionAnswer;
use Rikkei\Test\View\ViewTest;

class QuestionController extends Controller
{
    /**
     * get question to edit
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     * @throws \Throwable
     */
    public function getEdit(Request $request)
    {
        $valid = Validator::make($request->only('question_id'), [
            'question_id' => 'required|numeric'
        ]);
        if ($valid->fails()) {
            $result = [
                'success' => false,
                'message' => trans('test::test.no_data')
            ];
            return response()->json($result);
        }

        $langCode = $request->get('lang') || Session::get('locale');
        $questionId = $request->get('question_id');
        $isType4 = in_array($request->get('type'), ViewTest::ARR_TYPE_4);
        $qOrder = $request->get('q_order');
        if ($isType4) {
            $question = WrittenQuestion::find($questionId);
            $view = 'test::manage.includes.tr-written-item';
        } else {
            $question = Question::find($questionId);
            $view = 'test::manage.includes.tr-question-item';
        }
        if (!$question) {
            $result = [
                'success' => false,
                'message' => trans('test::test.not_found_item')
            ];
            return response()->json($result);
        }
        if ($qOrder === null || $qOrder === '') {
            $qOrder = 1;
            if ($testId = $request->get('test_id')) {
                $qOrder = TestQuestion::getQuestionOrder($testId, $question->id, true);
                $qOrder = $qOrder === null ? 1 : ($qOrder + 1);
            }
        };
        return view($view, [
            'qItem' => $question,
            'order' => intval($qOrder) - 1,
            'testId' => $request->get('test_id'),
            'currentLang' => $langCode,
        ])->render();
    }

    /**
     * save question content
     * @param Request $request
     * @return type
     */
    public function postSave(Request $request) {
        $valid = Validator::make($request->all(), [
            'question_id' => 'required|numeric'
        ]);
        if ($valid->fails()) {
            return response()->json(['success' => false, 'message' => trans('test::validate.no_data')]);
        }
        $questionId = $request->get('question_id');
        $content = $request->get('content');
        $question = Question::find($questionId);
        if (!$question) {
            return response()->json(['success' => false, 'message' => trans('test::validate.not_found_item')]);
        }
        $question->content = $content;
        $question->is_editor = 1;
        $question->save();
        return response()->json(['success' => true, 'content' => $question->content]);
    }

    /**
     * delete question by id
     * @param type $id
     * @return boolean
     */
    public function delete($id)
    {
        $type = Input::get('type');
        $isType4 = in_array($type, ViewTest::ARR_TYPE_4);
        if ($isType4) {
            WrittenQuestionAnswer::where('written_id', $id)->delete();
            DB::table('ntest_written_category')->where('written_id', $id)->delete();
            return WrittenQuestion::where('id', $id)->delete();
        }
        return Question::where('id', $id)->delete();
    }

    /**
     * view edit form question
     * @param type $id
     * @return type
     */
    public function fullEdit($id, Request $request)
    {
        $test = null;
        $isType4 = in_array($request->get('type'), ViewTest::ARR_TYPE_4);
        $langCode = $request->get('lang');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        if ($isType4) {
            $writtenQuestion = WrittenQuestion::findOrFail($id);
            if ($testId = $request->get('test_id')) {
                $test = Test::find($testId);
            }
            return view('test::manage.question.edit-written', compact('writtenQuestion', 'test'));
        }
        $question = Question::findOrFail($id);
        if ($testId = $request->get('test_id')) {
            $quesLangCode = $question->getLangCode($testId);
            //if current edit lang not equal question language
            if ($quesLangCode != $langCode) {
                $test = Test::findItemByLang($testId, $langCode);
                //if not found test lang => alert error message
                if (!$test) {
                    $request->merge([
                        'lang' => $quesLangCode,
                        'id' => $id,
                    ]);
                    $allLangs = CoreLang::allLang();
                    return redirect()->route('test::admin.test.question.full_edit', $request->query())
                        ->with('messages', [
                            'errors' => [trans('test::test.Not found test in this language', [
                                'lang' => isset($allLangs[$langCode]) ? $allLangs[$langCode] : '',
                            ])]
                        ]);
                }
                //find question lang through sort order
                $questionOrder = TestQuestion::getQuestionOrder($testId, $id); //$testId # $test->id
                $questionLang = TestQuestion::where('test_id', $test->id)
                    ->where('order', $questionOrder)
                    ->first();
                //don't have question lang ==> create new
                if (!$questionLang) {
                    $request->merge([
                        'q_order' => $questionOrder,
                    ]);
                    return redirect()->route('test::admin.test.question.create', $request->query());
                }
                $request->merge([
                    'test_id' => $test->id,
                    'id' => $questionLang->question_id,
                ]);
                //return edit page question lang
                return redirect()->route('test::admin.test.question.full_edit', $request->query());
            } else {
                $test = Test::find($testId);
            }
        }

        return view('test::manage.question.edit-multiple', compact('question', 'test'));
    }

    /**
     * submit edit form question
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function fullUpdate($id, Request $request)
    {
        $valid = Validator::make($request->all(), [
            //'question.content' => 'required'
        ], [
            //'question.content.required' => trans('validation.required', ['attribute' => 'Test content'])
        ]);
        if ($valid->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($valid->errors());
        }

        DB::beginTransaction();
        try {
            $question = Question::findOrFail($id);
            $questionData = $request->get('question');
            $questionData['is_editor'] = 1;
            if (!isset($questionData['multi_choice']) || !$questionData['multi_choice']) {
                $questionData['multi_choice'] = 0;
            }
            $question->update($questionData);

            $answerIds = [];
            $isType1 = in_array($question->type, ViewTest::ARR_TYPE_1);
            $isType2 = in_array($question->type, ViewTest::ARR_TYPE_2);

            //update answer
            $answers = $request->get('answers');
            $answersNew = $request->get('answers_new');
            //check duplicate answer
            if (!$isType1 && $answers && is_array($answers)) {
                $existsLabel = [];
                $allAnswers = $answers;
                if ($answersNew && is_array($answersNew)) {
                    $allAnswers = $answers + $answersNew;
                }
                foreach ($allAnswers as $ans) {
                    if (in_array($ans['label'], $existsLabel)) {
                        return redirect()->back()->withInput()
                                ->with('messages', ['errors' => [trans('test::test.Answer label is unique')]]);
                    } else {
                        array_push($existsLabel, $ans['label']);
                    }
                }
            }

            $answersCorrect = $request->get('answers_correct');
            $answersNewCorrect = $request->get('answers_new_correct');
            if ($answers && is_array($answers)) {
                foreach ($answers as $ansId => $ansData) {
                    if ($errors = Answer::validateMessage($question->type, $ansData)) {
                        return redirect()->back()->withInput()
                                ->with('messages', ['errors' => $errors]);
                    }
                    $answer = Answer::find($ansId);
                    if ($answer) {
                        $answer->update($ansData);
                        $isCorrect = 0;
                        if ($isType1 ||
                                (is_array($answersCorrect) && in_array($answer->id, $answersCorrect))) {
                            $isCorrect = 1;
                        }
                        $answerIds[$answer->id] = ['is_correct' => $isCorrect];
                    }
                }
            }

            //add new answer
            $answersCreated = [];
            if ($answersNew && is_array($answersNew)) {
                foreach ($answersNew as $key => $ansData) {
                    if ($errors = Answer::validateMessage($question->type, $ansData)) {
                        return redirect()->back()->withInput()
                                ->with('messages', ['errors' => $errors]);
                    }
                    $answer = Answer::create($ansData);
                    $isCorrect = 0;
                    if ($isType1 ||
                            (is_array($answersNewCorrect) && in_array($key, $answersNewCorrect))) {
                        $isCorrect = 1;
                    }
                    $answerIds[$answer->id] = ['is_correct' => $isCorrect];
                    $answersCreated['new_' . $key] = $answer;
                }
            }

            //update answers of question
            $question->answers()->sync($answerIds);

            //if type 2 update child question
            $currentChildIds = $question->childs()->lists('id')->toArray();
            $cQuestionIds = [];
            if ($answersCorrect && $isType2) {
                if ($errors = Answer::validateType2Answer($answersCorrect)) {
                    return redirect()->back()->withInput()
                            ->with('messages', ['errors' => $errors]);
                }
                $cQuestions = Question::whereIn('id', array_keys($answersCorrect))->get();
                if (!$cQuestions->isEmpty()) {
                    $childsContent = $request->get('childs_content');
                    $childsContent = $childsContent ? $childsContent : [];
                    foreach ($cQuestions as $cItem) {
                        if (isset($answersCorrect[$cItem->id])) {
                            $cAnswerId = $answersCorrect[$cItem->id];
                            //check answer new (new_newId)
                            $expCAsnwerId = explode('_', $cAnswerId);
                            if (count($expCAsnwerId) > 1 && isset($answersCreated[$cAnswerId])) {
                                //set answer id = answersCreated
                                $cAnswerId = $answersCreated[$cAnswerId]->id;
                            }
                            //save child content
                            if (isset($childsContent[$cItem->id])) {
                                $cItem->content = $childsContent[$cItem->id];
                                $cItem->is_editor = 1;
                                $cItem->save();
                            }

                            $cQuestionIds[$cItem->id] = $cItem;
                            $cItem->answers()
                                    ->sync([$cAnswerId => ['is_correct' => 1]]);
                        }
                    }
                }
            }
            //update child question new
            if ($answersNewCorrect && $isType2) {
                $childsNewContent = $request->get('childs_new_content');
                $childsNewContent = $childsNewContent ? $childsNewContent : [];
                if ($errors = Answer::validateType2Answer($answersNewCorrect)) {
                    return redirect()->back()->withInput()
                            ->with('messages', ['errors' => $errors]);
                }
                foreach ($answersNewCorrect as $key => $ansId) {
                    $cItem = Question::create([
                        'content' => isset($childsNewContent[$key]) ? $childsNewContent[$key] : null,
                        'is_editor' => 1,
                        'type' => ViewTest::ARR_TYPE_2[1],
                    ]);
                    $cQuestionIds[$cItem->id] = $cItem;
                    //check answer new (new_newId)
                    $expCAsnwerId = explode('_', $ansId);
                    if (count($expCAsnwerId) > 1 && isset($answersCreated[$ansId])) {
                        //set answer id = answersCreated
                        $ansId = $answersCreated[$ansId]->id;
                    }
                    $cItem->answers()
                            ->sync([$ansId => ['is_correct' => 1]]);
                }
            }
            //delete diff question
            Question::whereIn('id', array_diff($currentChildIds, array_keys($cQuestionIds)))->delete();
            //save new child questions
            $question->childs()->saveMany($cQuestionIds);
            //category
            $typeCats = $request->get('type_cats');
            if ($typeCats) {
                $typeCats = array_filter($typeCats);
            }
            $question->categories()->sync($typeCats);
            if ($typeCats) {
                $question->categories()->update(['is_temp' => 0]);
            }

            //sync question option langauge
            $testId = $request->get('test_id');
            if ($testId) {
                $question->syncOptionLang($testId, $request->only(['type_cats']));
            }

            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('test::test.save_data_success')]])
                    ->with('window_script', '<script>'
                            . 'window.opener.replaceOrAppendQuestion('. $question->id .', true);'
                            . 'window.close();'
                            . '</script>');
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * form create question
     * @return type
     */
    public function create(Request $request)
    {
        $langCode = $request->get('lang');
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $question = null;
        $test = null;
        $testId = $request->get('test_id');
        if ($testId) {
            $test = Test::find($request->get('test_id'));
        }
        $testClone = $test ? clone $test : null;
        $questionClone = null;
        $qOrder = $request->get('q_order');
        $hasQOrder = $qOrder !== null || $qOrder !== '';
        if (!$hasQOrder) {
            $qOrder = null;
        } else {
            if ($testId) {
                $test = Test::findItemByLang($testId, $langCode);
                $questionLang = TestQuestion::getByTestAndOrder($test->id, $qOrder); //$testId # $test->id
                // if has question lang then redirect to edit page
                if ($questionLang) {
                    $request->merge([
                        'test_id' => $test->id,
                        'id' => $questionLang->question_id,
                    ]);
                    return redirect()->route('test::admin.test.question.full_edit', $request->query());
                }
                //find original create from this question
                $questionClone = Question::findByTestAndOrder($testId, $qOrder); //$testId # $test->id
                if (!$questionClone && ($qLangId = $request->get('q_lang_id'))) {
                    $questionClone = Question::find($qLangId);
                }
                if ($questionClone) {
                    $question = clone $questionClone;
                    $question->id = null;
                    ViewTest::setQuestionOldData($questionClone);
                }
            }
        }
        $isType4 = in_array($request->get('type'), ViewTest::ARR_TYPE_4);
        if ($isType4) {
            return view('test::manage.question.edit-written', compact('question', 'questionClone', 'test', 'testClone', 'qOrder'));
        }
        return view('test::manage.question.edit-multiple', compact('question', 'questionClone', 'test', 'testClone', 'qOrder'));
    }

    /**
     * set question type
     * @param Request $request
     * @return type
     */
    public function updateType()
    {
        return redirect()->back()->withInput();
    }

    /**
     * store question
     * @param Request $request
     * @return type
     */
    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            //'question.content' => 'required',
            'question.type' => 'required',
        ]);
        $valid->setAttributeNames([
            //'question.content' => trans('test::test.question_content'),
            'question.type' => trans('test::test.question_type'),
        ]);
        if ($valid->fails()) {
            return redirect()->back()
                    ->withInput()->withErrors($valid->errors());
        }

        DB::beginTransaction();
        try {
            $questionData = $request->get('question');
            $isType1 = in_array($questionData['type'], ViewTest::ARR_TYPE_1);
            $isType2 = in_array($questionData['type'], ViewTest::ARR_TYPE_2);
            $isType4 = in_array($questionData['type'], ViewTest::ARR_TYPE_4);
            if (!$isType4) {
                $questionData['is_editor'] = 1;
                $questionData['is_temp'] = 1;
                if (!isset($questionData['multi_choice']) || !$questionData['multi_choice']) {
                    $questionData['multi_choice'] = 0;
                    if ($isType1) {
                        $questionData['multi_choice'] = 1;
                    }
                }
                $question = Question::create($questionData);
                $answerIds = [];

                //update answer
                $answersNew = $request->get('answers_new');
                //check duplicate
                $existsLabel = [];
                if ($answersNew && is_array($answersNew)) {
                    foreach ($answersNew as $ans) {
                        if (!isset($ans['label'])) {
                            continue;
                        }
                        if (in_array($ans['label'], $existsLabel)) {
                            return redirect()->back()->withInput()
                                ->with('messages', ['errors' => [trans('test::test.Answer label is unique')]]);
                        } else {
                            array_push($existsLabel, $ans['label']);
                        }
                    }
                }

                //add new answer
                $answersCreated = [];
                $answersNewCorrect = $request->get('answers_new_correct');
                if ($answersNew && is_array($answersNew)) {
                    foreach ($answersNew as $key => $ansData) {
                        if ($errors = Answer::validateMessage($question->type, $ansData)) {
                            return redirect()->back()->withInput()
                                ->with('messages', ['errors' => $errors]);
                        }
                        $ansData['is_temp'] = 1;
                        $answer = Answer::create($ansData);
                        $isCorrect = 0;
                        if ($isType1 ||
                            (is_array($answersNewCorrect) && in_array($key, $answersNewCorrect))) {
                            $isCorrect = 1;
                        }
                        $answerIds[$answer->id] = ['is_correct' => $isCorrect];
                        $answersCreated['new_' . $key] = $answer;
                    }
                }

                //update answers of question
                $question->answers()->attach($answerIds);

                //if type 2 update child question
                $cQuestionIds = [];
                //update child question new
                if ($answersNewCorrect && $isType2) {
                    $childsNewContent = $request->get('childs_new_content');
                    $childsNewContent = $childsNewContent ? $childsNewContent : [];
                    if ($errors = Answer::validateType2Answer($answersNewCorrect)) {
                        return redirect()->back()->withInput()
                            ->with('messages', ['errors' => $errors]);
                    }
                    foreach ($answersNewCorrect as $key => $ansId) {
                        $cItem = Question::create([
                            'content' => isset($childsNewContent[$key]) ? $childsNewContent[$key] : null,
                            'is_editor' => 1,
                            'type' => ViewTest::ARR_TYPE_2[1],
                            'is_temp' => 1
                        ]);
                        $cQuestionIds[$cItem->id] = $cItem;
                        //check answer new (new_newId)
                        $expCAsnwerId = explode('_', $ansId);
                        if (count($expCAsnwerId) > 1 && isset($answersCreated[$ansId])) {
                            //set answer id = answersCreated
                            $ansId = $answersCreated[$ansId]->id;
                        }
                        $cItem->answers()
                            ->sync([$ansId => ['is_correct' => 1]]);
                    }
                }
                //save new child questions
                $question->childs()->saveMany($cQuestionIds);
                //category
                $typeCats = $request->get('type_cats');
                $typeCats = $typeCats ? array_filter($typeCats) : [];
                if ($typeCats) {
                    $question->categories()->sync($typeCats);
                    $question->categories()->update(['is_temp' => 0]);
                }
            }
            //sync question option langauge
            $testId = $request->get('test_id');
            if ($testId) {
                if ($isType4) {
                    $writtenQuestion = WrittenQuestion::create([
                        'test_id' => $testId,
                        'content' => $questionData['content'],
                        'status' => $questionData['status'],
                    ]);
                    $typeCats = $request->get('type_cats');
                    DB::table('ntest_written_category')
                        ->insert([
                            'written_id' => $writtenQuestion->id,
                            'cat_id' => $typeCats
                        ]);
                    DB::commit();
                    return redirect()->back()->with('messages', ['success' => [trans('test::test.save_data_success')]])
                        ->with('window_script', '<script>'
                            . 'window.opener.createOrEditWrittenQuestion(' . $writtenQuestion->id . ', true, ' . $request->get('q_order') . ');'
                            . 'window.close();'
                            . '</script>');
                }

                $qOrder = $request->get('q_order');
                $hasQOrder = ($qOrder !== null && $qOrder !== '');
                //sync only has question order
                if ($hasQOrder) {
                    $question->syncOptionLang($testId, $request->only(['type_cats']));
                } else {
                    $qOrder = 0;
                }
                //update test question
                $existOrder = TestQuestion::where('test_id', $testId)
                    ->where('order', $qOrder)
                    ->first();
                if ($existOrder) {
                    $qOrder = (int)TestQuestion::where('test_id', $testId)->max('order') + 1;
                }
                TestQuestion::create([
                    'test_id' => $testId,
                    'question_id' => $question->id,
                    'order' => $qOrder,
                ]);
            }

            DB::commit();
            return redirect()->route('test::admin.test.question.full_edit', $question->id)
                ->with('messages', ['success' => [trans('test::test.save_data_success')]])
                ->with('window_script', '<script>'
                    . 'window.opener.replaceOrAppendQuestion('. $question->id .', true, '. $request->get('q_order') .');'
                    . 'window.close();'
                    . '</script>');
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    /**
     * ajax add new category
     * @param Request $request
     * @return object
     */
    public function addCategory(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'cat.name' => 'required',
            'cat.type_cat' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('test::validate.please_input_valid_value'), 422);
        }
        DB::beginTransaction();
        try {
            $testID = $request->get('test_id');
            $cat = Category::addIfNotExists($request->get('cat'), null, $testID);
            $testId = $request->get('test_id');
            if ($testId) {
                $test = Test::find($testId);
                if ($test) {
                    $testQCats = $test->question_cat_ids;
                    if (!$testQCats) {
                        $testQCats = [];
                    } else {
                        $testQCats = unserialize($testQCats);
                    }
                    if (!in_array($cat->id, $testQCats)) {
                        $testQCats[] = $cat->id;
                    }
                    $test->update(['question_cat_ids' => $testQCats]);
                }
            }
            DB::commit();
            return $cat;
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(trans('core::message.Error system, please try later!'), 500);
        }
    }

    /**
     * copy question to test
     * @param Request $request
     * @return type
     */
    public function copyToTest(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'test_id' => 'required',
            'question_ids' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json([
                'message' => trans('test::test.no_item')
            ], 422);
        }
        $testId = $request->get('test_id');
        $test = Test::find($testId);
        if (!$test) {
            return response()->json([
                'message' => trans('test::test.no_item')
            ], 404);
        }

        //get list question ids
        $questionIds = $request->get('question_ids');
        //check option replace question of test
        $replace = false;
        $optionCopy = $request->get('option_copy');
        if ($optionCopy && $optionCopy == 'replace') {
            $replace = true;
        }
        //init question to attach or sync to test
        $attachQuestionIds = [];
        $questions = Question::select('id')
                ->whereIn('id', $questionIds)->get();
        if ($questions->isEmpty()) {
            return response()->json([
                'message' => trans('test::test.no_item')
            ], 404);
        }

        try {
            DB::beginTransaction();
            if ($replace) {
                $test->questions(false)->update(['is_temp' => 1]);
                foreach ($questions as $key => $qItem) {
                    $attachQuestionIds[$qItem->id] = ['order' => $key];
                }
                $test->questions()->sync($attachQuestionIds);
            } else {
                $currentTotalQuestion = $test->questions->count();
                foreach ($questions as $key => $qItem) {
                    $attachQuestionIds[$qItem->id] = ['order' => $currentTotalQuestion + 1 + $key];
                }
                $test->questions()->syncWithoutDetaching($attachQuestionIds);
            }

            DB::commit();
            return response()->json([
                'message' => trans('test::test.copy_to_success')
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json([
                'message' => trans('test::test.test_error')
            ], 500);
        }
    }

    /**
     * export question selected
     * @param Request $request
     * @return type
     */
    public function exportExcel(Request $request)
    {
        $qIds = $request->get('questions');
        $writtenQuestionIds = $request->get('written');
        if (!$qIds && !$writtenQuestionIds) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('test::test.no_questions')]]);
        }

        $questionsType1 = Question::whereIn('id', $qIds)
                ->whereNotIn('type', ViewTest::ARR_TYPE_2)
                ->select('id', 'content', 'parent_id', 'type', 'is_editor', 'image_urls', 'explain', 'multi_choice', 'status')
                ->with(['answers', 'categories'])
                ->get();

        $questionsType2 = Question::whereIn('id', $qIds)
                ->whereIn('type', ViewTest::ARR_TYPE_2)
                ->select('id', 'content', 'parent_id', 'type', 'is_editor', 'explain', 'multi_choice', 'status')
                ->with([
                    'answers',
                    'categories',
                    'childs' => function ($query) {
                        $query->with(['answers', 'categories']);
                    }
                ])
                ->get();

        $writtenQuestions = WrittenQuestion::listWrittenQuestion($writtenQuestionIds);

        if ($questionsType1->isEmpty() && $questionsType2->isEmpty() && $writtenQuestions->isEmpty()) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('test::test.no_questions')]]);
        }

        $fileName = \Carbon\Carbon::now()->format('Ymd') . '_question_export';
        Excel::create($fileName, function ($excel) use ($questionsType1, $questionsType2, $writtenQuestions) {
            $arrKeyCats = array_keys(ViewTest::ARR_CATS);
            //sheet type 1,3,4
            $excel->sheet('type1', function ($sheet) use ($questionsType1, $arrKeyCats) {
                $arrAnswerLabel = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                $rowHeader = array_merge(['STT', 'Type', 'Content'], $arrAnswerLabel);
                $rowHeader = array_merge($rowHeader, ['Correct', 'Multi choice', 'Explain', 'Disable']);
                $rowCats = [];
                foreach ($arrKeyCats as $key) {
                    $rowCats[] = 'Category ' . $key;
                }
                $rowHeader = array_merge($rowHeader, $rowCats);
                $sheetData[] = $rowHeader;

                if (!$questionsType1->isEmpty()) {
                    foreach ($questionsType1 as $order => $qItem) {
                        $rowData = [
                            $order + 1,
                            $qItem->type,
                            ViewTest::decodeFormat($qItem->content),
                        ];

                        $isType1 = in_array($qItem->type, ViewTest::ARR_TYPE_1);
                        $answers = $qItem->answers;
                        $rowAnswerData = [];
                        $rowAnswerCorrect = [];
                        foreach ($arrAnswerLabel as $key => $label) {
                            $rowAnswerData[$key] = null;
                            if (!$answers->isEmpty()) {
                                foreach ($answers as $ans) {
                                    if ($ans->label == $label) {
                                        $rowAnswerData[$key] = ViewTest::decodeFormat($ans->content);
                                    }
                                    if ($ans->pivot->is_correct) {
                                        if ($isType1) {
                                            $rowAnswerCorrect[$ans->id] = $ans->content;
                                        } else {
                                            $rowAnswerCorrect[$ans->id] = $ans->label;
                                        }
                                    }
                                }
                            }
                        }
                        $rowAnswerData[] = implode(',', $rowAnswerCorrect);
                        $rowData = array_merge($rowData, $rowAnswerData);
                        $rowData = array_merge($rowData, [
                            $qItem->multi_choice ? 1 : '',
                            $qItem->explain,
                            $qItem->status == ViewTest::STT_DISABLE ? 1 : ''
                        ]);
                        //category
                        $arrayCats = $qItem->getArrCatIds();
                        foreach ($arrKeyCats as $key) {
                            $catData = null;
                            if (isset($arrayCats[$key]) && $arrayCats[$key]) {
                                $catData = implode(', ', $arrayCats[$key]);
                            }
                            $rowData[] = $catData;
                        }
                        $sheetData[] = $rowData;
                    }
                    $sheet->getStyle('A2:R100')->getAlignment()->setWrapText(true);
                    $sheet->setWidth('C', 100);
                    $sheet->fromArray($sheetData, null, 'A1', false, false);
                }
            });

            //sheet type 2
            if (!$questionsType2->isEmpty()) {
                $excel->sheet('type2', function ($sheet) use ($questionsType2, $arrKeyCats) {
                    $rowHead = ['ID', 'Content', 'Correct', 'Answer content', 'Explain', 'Disable'];
                    //category
                    foreach ($arrKeyCats as $key) {
                        $rowHead[] = 'Category ' . $key;
                    }
                    $sheetData[] = $rowHead;
                    foreach ($questionsType2 as $order => $qItem) {
                        $answers = $qItem->answers;
                        $childs = $qItem->childs;
                        $correctAnswers = [];
                        if (!$childs->isEmpty()) {
                            foreach ($childs as $idx => $cItem) {
                                $cAnswer = $cItem->answers()->first();
                                $correctAnswers[] = $cAnswer->id;
                                $rowData = [
                                    $order + 1,
                                    ViewTest::decodeFormat($cItem->content),
                                    $cAnswer->label,
                                    ViewTest::decodeFormat($cAnswer->content),
                                    $cItem->explain,
                                    $cItem->status == ViewTest::STT_DISABLE ? 1 : null
                                ];
                                if ($idx == 0) {
                                    $arrayCats = $qItem->getArrCatIds();
                                }
                                foreach ($arrKeyCats as $key) {
                                    $catData = null;
                                    if ($idx == 0 && isset($arrayCats[$key]) && $arrayCats[$key]) {
                                        $catData = implode(', ', $arrayCats[$key]);
                                    }
                                    $rowData[] = $catData;
                                }
                                $sheetData[] = $rowData;
                            }
                        }
                        if (!$answers->isEmpty()) {
                            foreach ($answers as $idx => $ans) {
                                if (!in_array($ans->id, $correctAnswers)) {
                                    $rowData = [
                                        $order + 1,
                                        null,
                                        $ans->label,
                                        ViewTest::decodeFormat($ans->content),
                                        null,
                                        null
                                    ];
                                    foreach ($arrKeyCats as $key) {
                                        $rowData[] = null;
                                    }
                                    $sheetData[] = $rowData;
                                }
                            }
                        }
                    }
                    $sheet->getStyle('A2:P100')->getAlignment()->setWrapText(true);
                    $sheet->setWidth('B', 50);
                    $sheet->fromArray($sheetData, null, 'A1', false, false);
                });
            }

            //sheet type 4
            if ($writtenQuestions) {
                $excel->sheet('type4', function ($sheet) use ($writtenQuestions) {
                    $sheetData[] = ['STT', 'Content', 'Category', 'Disable'];
                    if (!$writtenQuestions->isEmpty()) {
                        foreach ($writtenQuestions as $order => $qItem) {
                            $sheetData[] = [
                                $order + 1,
                                ViewTest::decodeFormat($qItem->content),
                                $qItem->cat_name,
                                $qItem->status == ViewTest::STT_DISABLE ? 1 : ''
                            ];
                        }
                    }
                    $sheet->getStyle('A2:P100')->getAlignment()->setWrapText(true);
                    $sheet->setWidth('B', 50);
                    $sheet->fromArray($sheetData, null, 'A1', false, false);
                });
            }
        })->export('xlsx');
    }

    public function updateWrittenQuestion($id, Request $request)
    {
        $questionData = $request->get('question');
        if (isset($questionData['type'])) {
            unset($questionData['type']);
        }
        $typeCats = $request->get('type_cats');
        DB::beginTransaction();
        try {
            $question = WrittenQuestion::findOrFail($id);
            $question->update([
                'content' => $questionData['content'],
                'status' => $questionData['status']
            ]);
            DB::table('ntest_written_category')->where('written_id', $question->id)->delete();
            if ($typeCats) {
                DB::table('ntest_written_category')->insert(['written_id' => $question->id, 'cat_id' => $typeCats]);
            }

            DB::commit();

            return redirect()->back()->with('messages', ['success' => [trans('test::test.save_data_success')]])
                ->with('window_script', '<script>'
                    . 'window.opener.createOrEditWrittenQuestion(' . $question->id . ');'
                    . 'window.close();'
                    . '</script>');
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()
                ->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }
}
