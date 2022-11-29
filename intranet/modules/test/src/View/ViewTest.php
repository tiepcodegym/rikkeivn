<?php

namespace Rikkei\Test\View;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\Permission;
use Carbon\Carbon;
use Session;
use Storage;

class ViewTest
{
    const PER_PAGE = 20;

    const ARR_TYPE_1 = ['type1', '1', 'Type 1'];
    const ARR_TYPE_2 = ['type2', '2', 'Type 2'];
    const ARR_TYPE_3 = ['type3', '3', 'Type 3'];
    const ARR_TYPE_4 = ['type4', '4', 'Type 4'];
    
    const ARR_CATS = [
        1 => 'category_1', 
        2 => 'category_2', 
        3 => 'category_3'
    ];

    const TESTER_PRIVATE = 1;
    const TESTER_PUBLISH = 2;

    const ANSWER_CORRECT = 1;
    const ANSWER_FALSE = 0;
    
    const STT_ENABLE = 1;
    const STT_DISABLE = 2;
    
    const GMAT_CODE = 'gmat';
    const SUBJECT_CODE = 'subject';
    const HELP_TEST_CODE = 'test_help';
    const SET_VALID_TIME = 1;
    const KEY_CURR_TEST = 'current_object_test';
    const TYPE2_TIME = '2018-09-24';
    
    const EX_FILE_LINK = 'https://docs.google.com/spreadsheets/u/1/d/1Gk1xVOnTIhfZ7fZHU3hhmsYSwycmUbh6/edit?usp=drive_web&ouid=114519526112308505880&dls=true';
    const DOC_FILE_LINK = 'https://docs.google.com/spreadsheets/d/10PekCntnl3HF7evc-UHvmN86x1_mlkeRgREcpsyFPEY/edit#gid=2072948309';
    
    const STR_PATTERN = [
        '/\`\`\`apache(.*?)\`\`\`/si',
        '/\`\`\`bash(.*?)\`\`\`/si',
        '/\`\`\`coffeescript(.*?)\`\`\`/si',
        '/\`\`\`cpp(.*?)\`\`\`/si',
        '/\`\`\`css(.*?)\`\`\`/si',
        '/\`\`\`cs(.*?)\`\`\`/si',
        '/\`\`\`diff(.*?)\`\`\`/si',
        '/\`\`\`html(.*?)\`\`\`/si',
        '/\`\`\`http(.*?)\`\`\`/si',
        '/\`\`\`ini(.*?)\`\`\`/si',
        '/\`\`\`java(.*?)\`\`\`/si',
        '/\`\`\`json(.*?)\`\`\`/si',
        '/\`\`\`js(.*?)\`\`\`/si',
        '/\`\`\`makefile(.*?)\`\`\`/si',
        '/\`\`\`markdown(.*?)\`\`\`/si',
        '/\`\`\`nginx(.*?)\`\`\`/si',
        '/\`\`\`objectivec(.*?)\`\`\`/si',
        '/\`\`\`perl(.*?)\`\`\`/si',
        '/\`\`\`php(.*?)\`\`\`/si',
        '/\`\`\`python(.*?)\`\`\`/si',
        '/\`\`\`ruby(.*?)\`\`\`/si',
        '/\`\`\`sql(.*?)\`\`\`/si',
        '/\`\`\`swift(.*?)\`\`\`/si',
        '/\`\`\`vbscript(.*?)\`\`\`/si',
        '/\`\`\`xhtml(.*?)\`\`\`/si',
        '/\`\`\`xml(.*?)\`\`\`/si',
        '/\`\`\`audio\s*(.*?)\s*\`\`\`/si',
        '/\`\`\`image\s*(.*?)\s*\`\`\`/si'
    ];
    
    const STR_REPLACE = [
        '<pre><code class="apache">$1</code></pre>',
        '<pre><code class="bash">$1</code></pre>',
        '<pre><code class="coffeescript">$1</code></pre>',
        '<pre><code class="cpp">$1</code></pre>',
        '<pre><code class="css">$1</code></pre>',
        '<pre><code class="cs">$1</code></pre>',
        '<pre><code class="diff">$1</code></pre>',
        '<pre><code class="html">$1</code></pre>',
        '<pre><code class="http">$1</code></pre>',
        '<pre><code class="ini">$1</code></pre>',
        '<pre><code class="java">$1</code></pre>',
        '<pre><code class="json">$1</code></pre>',
        '<pre><code class="javascript">$1</code></pre>',
        '<pre><code class="makefile">$1</code></pre>',
        '<pre><code class="markdown">$1</code></pre>',
        '<pre><code class="nginx">$1</code></pre>',
        '<pre><code class="objectivec">$1</code></pre>',
        '<pre><code class="perl">$1</code></pre>',
        '<pre><code class="php">$1</code></pre>',
        '<pre><code class="python">$1</code></pre>',
        '<pre><code class="ruby">$1</code></pre>',
        '<pre><code class="sql">$1</code></pre>',
        '<pre><code class="swift">$1</code></pre>',
        '<pre><code class="vbscript">$1</code></pre>',
        '<pre><code class="xhtml">$1</code></pre>',
        '<pre><code class="xml">$1</code></pre>',
        '<div class="ckeditor-html5-video"><video controls class="test-audio" src="$1"></video></div>',
        '<span class="c_image"><img src="$1" onerror="loadErrorImage(this)" alt="rikkei.vn"></span>'
    ];
    
    public static function codeFormat($content)
    {
        return preg_replace(self::STR_PATTERN, self::STR_REPLACE, $content);
    }
    
    public static function decodeFormat($content)
    {
        $strReplace = array_map(function ($item) {
            $item = str_replace('$1', '(.*?)', $item);
            $item = str_replace('/', '\/', $item);
            return '/' . $item . '/si';
        }, self::STR_REPLACE);
        
        $strPattern = array_map(function ($item) {
            $item = str_replace('(.*?)', ' $1', $item);
            $item = str_replace('/si', '', $item);
            $item = str_replace('/', '', $item);
            $item = str_replace('\\', '', $item);
            return $item;
        }, self::STR_PATTERN);
        
        $content = html_entity_decode($content);
        $content = str_replace('onerror=\"loadErrorImage(this)\"', '', $content);
        $content = preg_replace('/<p>(.*?)<\/p>/si', PHP_EOL.'$1'.PHP_EOL, $content);
        $content = preg_replace('/<div>(.*?)<\/div>/si', PHP_EOL.'$1'.PHP_EOL, $content);
        $content = str_replace('<br />', PHP_EOL, $content);
        $content = str_replace('<br/>', PHP_EOL, $content);
        $content = str_replace('<br>', PHP_EOL, $content);
        $content = preg_replace('/<span class="c_image"><img(.*?)src="(.*?)"(.*?)><\/span>/si', '<img src="$2">', $content);
        $content = preg_replace(
            '/<div(.*?)class="ckeditor-html5-video"(.*?)><video(.*?)src="(.*?)"(.*?)>(.*?)<\/video><\/div>/si',
            '<video src="$4"></video>',
            $content
        );
        $content = preg_replace('/class="language-(.*?)"/si', 'class="$1"', $content);
        $content = preg_replace("/<pre>(.*?)<code/si", "<pre><code", $content);
        //video, image
        $content = preg_replace('/<img src="(.*?)">/si', '```image $1 ```', $content);
        $content = preg_replace('/<video src="(.*?)"><\/video>/si', '```audio $1 ```', $content);
        
        return preg_replace($strReplace, $strPattern, $content);
    }
    
    /**
     * list question status label
     * @return type
     */
    public static function listStatusLabel()
    {
        return [
            self::STT_ENABLE => 'Enable',
            self::STT_DISABLE => 'Disable'
        ];
    }
    
    /**
     * list question types
     * @return type
     */
    public static function listQuestionTypes()
    {
        return [
            '1' => 'Type 1',
            '2' => 'Type 2',
            '3' => 'Type 3',
            '4' => 'Written Test',
        ];
    }
    
    /**
     * list array types
     * @param type $type
     * @return type
     */
    public static function arrayTypes($type) {
        $arrTypes = [
            self::ARR_TYPE_1,
            self::ARR_TYPE_2,
            self::ARR_TYPE_3,
            self::ARR_TYPE_4
        ];
        foreach ($arrTypes as $types) {
            if (in_array($type, $types)) {
                return $types;
            }
        }
        return self::ARR_TYPE_1;
    }
    
    /**
     * random answers
     *
     * @param collection $answers answer need random
     * @param array $otherRanAnswers answer after random of other lang question
     * @param array $qIndexValues current test question index
     * @param answer $originOtherAnswers default answer of other lang question
     * @param int|null $questionId
     * @return collection
     */
    public static function shuffleAnswers(
        $answers,
        $otherRanAnswers,
        $qIndexValues,
        $originOtherAnswers,
        $questionId = null
    )
    {
        if (!$questionId) {
            $questionId = $answers->first()->pivot->question_id;
        }
        $arrayLabels = $answers->pluck('label', 'id')->toArray();
        //search index of current question in random question index
        $qIndex = array_search($questionId, $qIndexValues);
        $keyRanAnswers = array_keys($otherRanAnswers);
        if ($qIndex !== false && isset($keyRanAnswers[$qIndex])) {
            //maping link theo thứ tự $qIndex của câu hỏi đc lưu sesion trước đó
            $otherQuesId = $keyRanAnswers[$qIndex];
            $otherRands = $otherRanAnswers[$otherQuesId]; // đáp án đã được random trước đó (của câu hỏi ở ngôn ngữ khác)
            $originRands = $originOtherAnswers[$otherQuesId]->pluck('label', 'id')->toArray(); // đáp án chưa đc random trước đó
            $arraySave = [];
            $keyOriginRands = array_keys($originRands);
            foreach ($otherRands as $rAnsId => $rLabel) {
                $oLabel = $originRands[$rAnsId]; //lable chưa đc random
                $newQId = array_search($oLabel, $arrayLabels); // tìm câu hỏi trước khi random
                if ($newQId !== false) {
                    $arraySave[$newQId] = $rLabel; //random theo thứ tự cho trước
                    $ansIdx = array_search($rAnsId, $keyOriginRands); // tìm key trong $answers
                    if ($ansIdx !== false && isset($answers[$ansIdx])) {
                        $answers[$ansIdx]->label = $rLabel; //set random label cho nó
                    }
                }
            }
            return [
                'answers' => $answers->sortBy('label'),
                'save' => $arraySave
            ];
        }

        shuffle($arrayLabels);
        $arraySave = [];
        foreach ($answers as $key => $ans) {
            $ans->label = $arrayLabels[$key];
            $answers[$key] = $ans;
            $arraySave[$ans->id] = $arrayLabels[$key];
        }
        //sort by value (label)
        asort($arraySave);
        return [
            'answers' => $answers->sortBy('label'),
            'save' => $arraySave
        ];
    }

    /**
     * generate answer from array random lables
     *
     * @param array $arrayLabels random label [answerId => label]
     * @param collection $answers default anaswers of questions
     * @return collection
     */
    public static function genAnswerFromArrLabels($arrayLabels, $answers)
    {
        foreach ($answers as $key => $ans) {
            $ans->label = $arrayLabels[$ans->id];
            $answers[$key] = $ans;
        }
        return $answers->sortBy('label');
    }

    public static function getAnswersByQuestionIds($questionIds, $testId)
    {
        $answers = \Rikkei\Test\Models\Question::getListAnswers($questionIds);
        return $answers;
    }

    /**
     * short link
     * @param string $link
     * @return string
     */
    public static function shortLink($link)
    {
        $start = 7;
        $end = 6;
        $len = strlen($link);
        if ($len > ($start + $end)) {
            $link = substr($link, 0, $start)
                    . '...' 
                    . substr($link, $len - $end, $end);
        }
        return $link;
    }
    
    /**
     * convert boolean value to string
     * @param type $value
     * @return string
     */
    public static function convertBool($value)
    {
        if ($value === true) {
            return 'TRUE';
        } elseif ($value === false) {
            return 'FALSE';
        } else {
            return $value;
        }
    }
    
    /**
     * remove all html tag
     */
    public static function stripAllTags($string, $removeBreak = false) 
    {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);
        if ($removeBreak)
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        return trim($string);
    }
    
    /**
     * trim long text
     * @param type $text
     * @param int $numLine
     * @param int $numWords
     * @param int $numCh
     * @param string $more
     * @return string
     */
    public static function trimWords($text, $options = [], $more = null, &$hasMore = false) 
    {
        $numLine = isset($options['num_line']) ? $options['num_line'] : 2; 
        $numWords = isset($options['num_word']) ? $options['num_word'] : 50; 
        $numCh = isset($options['num_ch']) ? $options['num_ch'] : 230;
        
        if (null === $more) {
            $more = '...';
        }
        $textReplace = preg_replace([
            '/<pre>(.*?)<code (.*?)<\/code>(.*?)<\/pre>/si',
            '/<img(.*?)>/si',
            '/<video(.*?)>(.*?)<\/video>/si'
        ], [
            '...[CODE]...',
            '...[IMAGE]...',
            '...[AUDIO]...'
        ], $text);
        if ($textReplace != $text) {
            $hasMore = true;
            $text = $textReplace;
        }
        
        $arrayLines = preg_split("/[\r\n]+/", $text, $numLine + 1, PREG_SPLIT_NO_EMPTY);
        $hasMoreLine = count($arrayLines) > $numLine;
        if ($hasMoreLine) {
            array_pop($arrayLines);
            $text = implode("\r\n", $arrayLines);
            $hasMore = true;
        }
        
        $text = self::stripAllTags($text);
        
        if (mb_strlen($text, 'utf-8') > $numCh) {
            $hasMore = true;
            return mb_substr($text, 0, $numCh, 'utf-8') . $more;
        }
        
        $wordsArray = preg_split("/[\n\r\t ]+/", $text, $numWords + 1, PREG_SPLIT_NO_EMPTY);
        $sep = ' ';
        if (count($wordsArray) > $numWords) {
            array_pop($wordsArray);
            $text = implode($sep, $wordsArray);
            $hasMore = true;
            return $text . $more;
        }
        if ($hasMoreLine) {
            $text .= $more;
        }
        return $text;
    }
    
    /**
     * collect list array category
     * @param type $row
     * @param array $arrayCats
     * @param type $name
     */
    public static function collectCategory($listCats, &$arrayCats)
    {
        foreach (array_keys(self::ARR_CATS) as $key) {
            if (isset($listCats[$key]) && $listCats[$key]) {
                foreach ($listCats[$key] as $catId => $catName) {
                    if (!isset($arrayCats[$key][$catId])) {
                        $arrayCats[$key][$catId] = $catName;
                    }
                }
            }
        }
    }
    
    /**
     * generate question option display
     * @param object $test
     * @param collection $questions
     * @return mixed boolean|array
     */
    public static function genDisplayQuestion($test, &$questions)
    {
        $isRandomQuestion = $test->random_order;
        if (!$isRandomQuestion && !$test->limit_question) {
            return false;
        }

        $questionIndexKey = 'question_index_' . $test->group_id;
        $questionIndex = Session::get($questionIndexKey);
        $questionIndex = $questionIndex ? $questionIndex : [];
        //has other test session same group
        if (!isset($questionIndex[$test->id])) {
            if ($questionIndex) {
                $firstTestId = array_keys($questionIndex)[0];
                $firstQIndexs = $questionIndex[$firstTestId];
                $qTestOrders = array_keys($firstQIndexs);
                //list by order [order => question]
                $groupQuestions = $questions->groupBy('pivot.order');

                $rsQuestions = [];
                $qIndex = 0;
                $sessionQIndexs = [];
                foreach ($qTestOrders as $order) {
                    if (isset($groupQuestions[$order])) {
                        $qItemOrder = $groupQuestions[$order]->first();
                        $rsQuestions[$qIndex] = $qItemOrder;
                        $sessionQIndexs[$order] = $qItemOrder->id;
                    }
                    $qIndex++;
                }
                $questionIndex[$test->id] = $sessionQIndexs;
                $questions = collect($rsQuestions);
                Session::put($questionIndexKey, $questionIndex);

                return $questionIndex[$test->id];
            }
            $questionIndex[$test->id] = null;
        }

        if ($questionIndex[$test->id] === null) {
            $questionsLen = $questions->count();
            $ranTotalQuestion = $test->total_question;

            //check display option
            $displayOptions = $test->getDisplayOption();
            //keep question index, chỉ số câu hỏi cần giữ lại
            $keepIndexs = [];
            $questionOptions = [];
            $forgotQuestions = [];
            if ($displayOptions && $test->limit_question && $ranTotalQuestion < $questionsLen) {
                foreach ($displayOptions as $options) {
                    $number = $options['value'];
                    $indexs = [];
                    unset($options['value']);
                    foreach ($questions as $idx => $qItem) {
                        if (isset($questions->id)) {
                            $categories = $qItem->categories;
                            if ($categories) {
                                $catIds = $categories->lists('id')->toArray();
                                //nếu câu hỏi 1 category trùng với categories của options
                                $filterOptions = array_filter($options);
                                if (count(array_intersect($catIds, $filterOptions)) == count($filterOptions)) {
                                    $indexs[] = $idx;
                                }
                            }
                        }
                    }

                    //unset question not in options
                    if ($indexs && $number <= count($indexs)) {
                        //lấy random $number phần tử trong mảng $indexs
                        $keepIdxs = (array) array_rand($indexs, $number);
                        foreach ($indexs as $key => $idx) {
                            if (in_array($key, $keepIdxs) && !in_array($idx, $keepIndexs)) {
                                $keepIndexs[] = $idx;
                                $questionOptions[$idx] = $questions[$idx];
                            } else {
                                //giữ lại câu hỏi đã xóa ko phù hợp với random trên
                                $forgotQuestions[$idx] = $questions[$idx];
                            }
                            //bỏ các câu hỏi đã lấy ở display options
                            $questions->forget($idx);
                        }
                    }
                }
            }

            //lấy random số câu hỏi còn lại
            if ($questionOptions && $test->limit_question) {
                $subRandTotal = $ranTotalQuestion - count($questionOptions);
                //nếu số câu hỏi còn lại > tổng số câu hỏi còn lại thì push lại câu hỏi đã xóa
                if ($subRandTotal > $questions->count()) {
                    $questions = $questions->union(collect($forgotQuestions));
                }
                if ($subRandTotal == 0) {
                    $questions = collect($questionOptions);
                } else {
                    if ($subRandTotal <= $questions->count()) {
                        //nếu chỉ 1 item hàm random return object not collection
                        if ($subRandTotal == 1) {
                            $questionKeys = $questions->keys()->toArray();
                            $randKey = array_rand($questionKeys, 1);
                            $questions = collect([$questionKeys[$randKey] => $questions[$questionKeys[$randKey]]]);
                        } else {
                            $questions = $questions->random($subRandTotal);
                        }
                        $questions = $questions->union(collect($questionOptions));
                    }
                }
            }

            //random question
            if (($ranTotalQuestion >= $questionsLen || !$test->limit_question) 
                    && $isRandomQuestion) {
                $questionRands = $questions->shuffle();
            } else {
                //random but keep index in $keepIndexs
                if ($questions->count() > 1) {
                    $questionRands = $questions->random($ranTotalQuestion);
                    if ($ranTotalQuestion < 2) {
                        $questionRands = collect([$questionRands]);
                    } else {
                        $questionRands = $questionRands->shuffle();
                    }
                } else {
                    $questionRands = $questions;
                }
            }

            $questions = $questionRands;
            $questionIndex[$test->id] = $questions->pluck('id', 'pivot.order')->toArray();
            Session::put($questionIndexKey, $questionIndex);
        } else {
            $groupQuestions = $questions->groupBy('id');
            $rsQuestions = [];
            $qIndex = 0;
            foreach ($questionIndex[$test->id] as $qId) {
                if (isset($groupQuestions[$qId])) {
                    $rsQuestions[$qIndex] = $groupQuestions[$qId]->first();
                }
                $qIndex++;
            }
            $questions = collect($rsQuestions);
        }

        return $questionIndex[$test->id];
    }

    /**
     * check valid image source and notify error
     * @param string $url
     * @param string $title
     * @param int $rowIndex
     * @throws \Exception
     */
    public static function checkValidSource($url, $title = '', $rowIndex = 0)
    {
        $client = new Client();
        try {
            $res = $client->request('GET', $url);
            if ($res->getStatusCode() != 200) {
                throw new \Exception(trans('test::test.image_link_not_found', ['link' => $url]) . ', sheet "' . $title . '", row "' . ($rowIndex + 2) . '"');
            }
        } catch (ConnectException $ex) {
            throw new \Exception(trans('test::test.image_link_not_found', ['link' => $url]) . ', sheet "' . $title . '", row "' . ($rowIndex + 2) . '"');
        } catch (RequestException $ex) {
            throw new \Exception(trans('test::test.no_permission_to_access_link', ['link' => $url]) . ', sheet "' . $title . '", row "' . ($rowIndex + 2) . '"');
        }
    }

    /**
     * get help page link
     */
    public static function getHelpLink()
    {
        $helpTestId = CacheHelper::get('help_test_parent_id');
        if (!$helpTestId) {
            $helpPage = \Rikkei\Help\Model\Help::where('title', 'Test')->first();
            if ($helpPage) {
                $helpTestId = $helpPage->id;
                CacheHelper::put('help_test_parent_id', $helpPage->id);
            }
        }
        if (!$helpTestId) {
            return null;
        }
        return route('help::display.help.view', ['id' => $helpTestId]);
    }

    /**
     * check permission
     */
    public static function hasPermiss($employeeId = null, $route = null)
    {
        $isScopeCompany = Permission::getInstance()->isScopeCompany(null, $route);
        if (!$employeeId && $isScopeCompany) {
            return true;
        }
        if ($isScopeCompany) {
            return true;
        }
        if (Permission::getInstance()->isScopeTeam(null, $route)) {
            $currentUser = Permission::getInstance()->getEmployee();
            $teamIds = $currentUser->getTeamPositons()->lists('team_id')->toArray();
            $hasEmployee = \Rikkei\Team\Model\TeamMember::whereIn('team_id', $teamIds)
                    ->where('employee_id', $employeeId)
                    ->first();
            if ($hasEmployee) {
                return true;
            }
        }
        if (Permission::getInstance()->isScopeSelf(null, $route)) {
            $currentUser = Permission::getInstance()->getEmployee();
            return $employeeId === $currentUser->id;
        }
        return false;
    }

    /*
     * check invalid time to test
     */
    public static function checkValidTime($test, $returnMess = false)
    {
        if ($test->set_valid_time == self::SET_VALID_TIME) {
            $timeStart = $test->time_start;
            $timeEnd = $test->time_end;
            $now = Carbon::now()->second(0);

            $timeStart = Carbon::parse($timeStart);
            $timeEnd = Carbon::parse($timeEnd);
            if ($now->lt($timeStart)) {
                $message = trans('test::test.less_time_to_test');
            } elseif ($now->gt($timeEnd)) {
                $message = trans('test::test.time_test_is_expired');
            } else {
                $message = null;
            }
            if ($returnMess) {
                return $message;
            }
            if ($message) {
                echo view('test::test.view_error', compact('message', $message));
                exit;
            }
        }
    }

    /*
     * update test temp data
     */
    public static function updateTestTemp($testTemp, $options = [])
    {
        if (!$testTemp) {
            return;
        }
        $test = $options['test'];
        $questionIndex = $options['questionIndex'];
        $isRandomAnswer = $options['isRandomAnswer'];
        $arrRanAnswers = $options['arrRanAnswers'];

        $saveTestTemp = false;
        if ($questionIndex) {
            $testTemp->question_index = implode($questionIndex, ',');
            $testTemp->total_question = $test->total_question;
            $saveTestTemp = true;
        }
        if ($isRandomAnswer && is_array($arrRanAnswers)) {
            $testTemp->random_labels = serialize($arrRanAnswers);
            $saveTestTemp = true;
        }
        if ($saveTestTemp) {
            $testTemp->save();
        }
    }

    /*
     * make directory
     */
    public static function makeDirectory($dir)
    {
        $uploadDir = 'tests/' . $dir;
        if (Storage::disk('public')->exists($uploadDir)) {
            return;
        }
        Storage::disk('public')->makeDirectory($uploadDir);
        @chmod(storage_path('app/public/' . $uploadDir), 0777);
    }

    /*
     * make random unique dir
     */
    public static function makeRandDir()
    {
        $uploadDir = 'tests/';
        $dir = str_random(8);
        if (!Storage::disk('public')->exists($uploadDir . $dir)) {
            return $uploadDir . $dir;
        }
        return self::makeRandDir();
    }

    /*
     * get file extension by path
     */
    public static function getExtensionByPath($path)
    {
        $arrPath = explode('.', $path);
        $len = count($arrPath);
        if ($len < 2) {
            return null;
        }
        return $arrPath[$len - 1];
    }

    /*
     * upload image from excel sheet
     */
    public static function uploadTempImageBySheets($dataSheets = [])
    {
        $pathUpload = 'tests/temp/user_' . auth()->id();
        Storage::disk('public')->deleteDirectory($pathUpload);
        if ($dataSheets['type1'] && $dataSheets['type2'] && !Storage::disk('public')->exists($pathUpload)) {
            self::makeDirectory($pathUpload);
        }
        foreach ($dataSheets as $type => $sheets) {
            if (!$sheets) {
                continue;
            }
            if (is_array($sheets)) {
                $sheets = $sheets[0];
            }
            $drawCollection = $sheets->getDrawingCollection();
            foreach ($drawCollection as $draw) {
                $imgPath = $draw->getPath();
                $imgDesc = trim($draw->getDescription());
                $ext = self::getExtensionByPath($imgPath);
                if (in_array($ext, ['jpeg', 'png', 'jpg', 'gif'])) {
                    Storage::disk('public')->put($pathUpload . '/'. $type .'_' . $imgDesc . '/' . $imgDesc . '.' . $ext, file_get_contents($imgPath), 'public');
                }
            }
        }
        return self::makeRandDir();
    }

    /*
     * move temp image and get image url
     */
    public static function getTempImageUrl($rowOrder, $randDir)
    {
        if (!$rowOrder) {
            return null;
        }
        $pathUpload = 'tests/temp/user_' . auth()->id();
        $files = Storage::disk('public')->files($pathUpload . '/' . $rowOrder);
        if (!$files) {
            return null;
        }
        $file = $files[0];
        $fileName = basename($file);
        $moveDir = $randDir . '/' . $rowOrder;
        if (Storage::disk('public')->exists($moveDir)) {
            Storage::disk('public')->deleteDirectory($moveDir);
        }
        $movePath = $moveDir . '/' . $fileName;
        Storage::disk('public')->move($file, $movePath);

        return '<span class="c_image"><img src="/storage/'. $movePath .'" onerror="loadErrorImage(this)" alt="rikkei.vn"></span>';
    }

    /*
     * delete temp image folder
     */
    public static function cronDelTempImage()
    {
        $pathTemp = 'tests/temp';
        if (Storage::disk('public')->exists($pathTemp)) {
            Storage::disk('public')->deleteDirectory($pathTemp);
        }
        $allFiles = Storage::disk('public')->allFiles('tests');
        if (!$allFiles) {
            return;
        }
        foreach ($allFiles as $file) {
            $countQ = \Rikkei\Test\Models\Question::where('content', 'like', '%'. $file .'%')->count();
            if ($countQ == 0) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    public static function getQuestionsByIds($questionIds = [])
    {
        return \Rikkei\Test\Models\Question::whereIn('id', $questionIds)->get();
    }

    /**
     * set old data for create question page
     *
     * @param Object $questionClone
     */
    public static function setQuestionOldData($questionClone)
    {
        $isCloneType2 = in_array($questionClone->type, ViewTest::ARR_TYPE_2);
        $oldAnswerNew = old('answers_new') ? old('answers_new') : [];
        $answersNewCorrectOld = [];
        $answers = null;
        if (!$oldAnswerNew) {
            $answers = $questionClone->answers;
            if (!$answers->isEmpty()) {
                foreach ($answers as $key => $ans) {
                    $oldAnswerNew[] = ['label' => $ans->label, 'content' => $ans->content];
                    if ((int) $ans->pivot->is_correct) {
                        $answersNewCorrectOld[] = $key;
                    }
                }
                Session::flash('_old_input.answers_new', $oldAnswerNew);
                if ($answersNewCorrectOld) {
                    Session::flash('_old_input.answers_new_correct', $answersNewCorrectOld);
                }
            }
        }
        //child Question
        $childNewContentOld = old('childs_new_content') ? old('childs_new_content') : [];
        if (!$childNewContentOld && $isCloneType2) {
            $qChilds = $questionClone->childs()->with('answers')->get();
            if (!$qChilds->isEmpty()) {
                $answersNewCorrectOld = [];
                if ($answers === null) {
                    $answers = $questionClone->answers;
                }
                $aryAnswerIds = $answers->isEmpty() ? [] : $answers->pluck('id')->toArray();
                foreach ($qChilds as $key => $cItem) {
                    $childContent = $cItem->content;
                    if (!$cItem->is_editor) {
                        $childContent = nl2br($childContent);
                    }
                    $childContent = htmlentities($childContent);
                    $childNewContentOld[$key + 1] = $childContent;
                    $cAnswer = $cItem->answers->first();
                    $keyAns = $cAnswer ? array_search($cAnswer->id, $aryAnswerIds) : false;
                    if ($keyAns !== false) {
                        $answersNewCorrectOld[$key + 1] = 'new_' . $keyAns;
                    }
                }
                Session::flash('_old_input.childs_new_content', $childNewContentOld);
                Session::flash('_old_input.answers_new_correct', $answersNewCorrectOld);
            }
        }
    }
}

