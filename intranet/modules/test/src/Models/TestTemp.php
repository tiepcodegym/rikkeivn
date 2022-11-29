<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\Models\Result;
use Rikkei\Test\Models\Question;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Rikkei\Test\View\ViewTest;
use Rikkei\Test\Models\LangGroup;
use Illuminate\Support\Facades\Session;

class TestTemp extends CoreModel
{
    protected $table = 'ntest_test_temps';
    protected $fillable = ['test_id', 'employee_email', 'employee_name', 'candidate_id', 'leaved_at', 'created_at', 'updated_at'];
    protected $dates = ['leaved_at'];
    protected $primaryKey = ['test_id', 'employee_email'];
    public $incrementing = false;

    public $colsSync = ['leaved_at', 'created_at'];

    /*
     * Set the keys for a save update query.
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }
        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }
        return $query;
    }

    /**
     * Get the primary key value for a save query.
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }
        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }
        return $this->getAttribute($keyName);
    }

    /**
     * get test
     * @return type
     */
    public function test() {
        return $this->belongsTo('\Rikkei\Test\Models\Test', 'test_id');
    }
    
    /**
     * get employee by email
     * @return type
     */
    public function employee() {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_email', 'email');
    }
    
    /**
     * get test time
     * @return int
     */
    public function getTestTime() {
        $test = $this->test;
        if ($test) {
            return $test->time;
        }
        return 0;
    }

    
    /**
     * check test temp exists
     * @param type $email
     * @param type $test_id
     * @return boolean
     */
    public static function checkTest($email, $testId)
    {
        return self::where('employee_email', $email)
                ->where('test_id', $testId)
                ->orderBy('created_at', 'desc')
                ->first();
    }
    
    /**
     * cron job save test result from test temp if passed the time
     * @return type
     */
    public static function submitResult() {
        $now = Carbon::now()->getTimestamp();
        $temps = self::select('temp.*', 'test.time as test_time')
            ->from(self::getTableName() . ' as temp')
            ->join(Test::getTableName() . ' as test', 'temp.test_id', '=', 'test.id')
            ->where('temp.created_at', '>=', Carbon::now()->subDay()->toDateTimeString())
            ->get();
        if ($temps->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach ($temps as $temp) {
                if (Result::checkDoTest($temp->employee_email, $temp->test_id, $temp->candidate_id)) {
                    continue;
                }
                $testTime = $temp->test_time + 5;
                $endTime = $temp->created_at->addMinute($testTime)->getTimeStamp();
                if ($endTime >= $now) {
                    continue;
                }
                $employeeName = $temp->employee_name;
                if (!$employeeName) {
                    $employeeName = explode('@', $temp->employee_email)[0];
                }
                $dataResult = [
                    'employee_id' => $temp->employee_id,
                    'candidate_id' => $temp->candidate_id,
                    'employee_email' => $temp->employee_email,
                    'employee_name' => $employeeName,
                    'tester_type' => (!$temp->candidate_id && !$temp->employee_id) ? ViewTest::TESTER_PUBLISH : ViewTest::TESTER_PRIVATE,
                    'test_id' => $temp->test_id,
                    'question_index' => $temp->question_index,
                    'random_labels' => $temp->random_labels,
                    'total_question' => $temp->total_question
                ];
                //get questions
                $questions = Question::select('q.*')
                        ->from(Question::getTableName() . ' as q')
                        ->with('childs')
                        ->join('ntest_test_question as tq', 'q.id', '=', 'tq.question_id')
                        ->leftJoin(Question::getTableName() . ' as qc', 'qc.parent_id', '=', 'q.id')
                        ->where('tq.test_id', $temp->test_id)
                        ->where('q.status', ViewTest::STT_ENABLE)
                        ->groupBy('q.id')
                        ->get();
                $arrayQuestions = [];
                if (!$questions->isEmpty()) {
                    foreach ($questions as $qItem) {
                        $arrayQuestions[$qItem->id] = $qItem;
                        if (!$qItem->childs->isEmpty()) {
                            foreach ($qItem->childs as $child) {
                                $arrayQuestions[$child->id] = $child;
                            }
                        }
                    }
                }
                //check answer
                $strAnswers = $temp->str_answers;
                $answers = $strAnswers ? json_decode($strAnswers, true) : [];
                //filter answers
                if ($answers) {
                    foreach ($answers as $qId => $ansIds) {
                        $qItem = isset($arrayQuestions[$qId]) ? $arrayQuestions[$qId] : null;
                        if (!$qItem) {
                            continue;
                        }
                        if ($qItem->parent_id && in_array($qItem->type, ViewTest::ARR_TYPE_2)) {
                            if (!isset($answers[$qItem->parent_id])) {
                                $answers[$qItem->parent_id] = [];
                            }
                            if ($ansIds) {
                                foreach ($ansIds as $ansId) {
                                    $answers[$qItem->parent_id][$qId] = $ansId;
                                }
                            }
                            unset($answers[$qId]);
                        }
                    }
                }

                Result::saveSubmitResult($dataResult, $questions, $answers);
                self::deleteTemp($temp->employee_email, $temp->test_id);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

    public static function haveTemp($email)
    {
        $temp = self::where('employee_email', '=', $email);
        return $temp->count();
    }

    /**
     * delte all test temp same lang group
     *
     * @param string $email
     * @param int $testId
     * @return boolean
     */
    public static function deleteTemp($email, $testId)
    {
        $groupTbl = LangGroup::getTableName();
        $collect = self::select('temp.*')
            ->from(self::getTableName() . ' as temp')
            ->where('temp.employee_email', $email)
            ->join($groupTbl . ' as group', 'group.test_id', '=', 'temp.test_id')
            ->whereIn('group.group_id', function ($query) use ($testId) {
                $query->select('group_id')
                    ->from(LangGroup::getTableName())
                    ->where('test_id', $testId);
            })
            ->get();

        if ($collect->isEmpty()) {
            return false;
        }
        foreach ($collect as $temp) {
            $temp->delete();
        }
        return true;
    }

    /**
     * get item testing by email
     *
     * @param string $email
     * @param int $testType
     * @param string $langCode
     * @return Object
     */
    public static function getTestTemp($email, $testType, $langCode = null)
    {
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        return self::select('temp.*')
            ->from(self::getTableName() . ' as temp')
            ->join(Test::getTableName() . ' as test', 'temp.test_id', '=', 'test.id')
            ->join(LangGroup::getTableName() . ' as group', function ($join) use ($langCode) {
                $join->on('group.test_id', '=', 'temp.test_id')
                    ->where('group.lang_code', '=', $langCode);
            })
            ->where('temp.employee_email', $email)
            ->where('test.type_id', $testType)
            ->first();
    }

    /**
     * get all test temps by test type id
     *
     * @param string $email
     * @param int $testType
     * @return collection
     */
    public static function getAllTestTempByType($email, $testType)
    {
        return self::select('temp.*', 'group.lang_code', 'group.group_id')
            ->from(self::getTableName() . ' as temp')
            ->join(Test::getTableName() . ' as test', 'temp.test_id', '=', 'test.id')
            ->join(LangGroup::getTableName() . ' as group', 'group.test_id', '=', 'temp.test_id')
            ->where('temp.employee_email', $email)
            ->where('test.type_id', $testType)
            ->groupBy('temp.test_id', 'temp.employee_email')
            ->get();
    }

    /**
     * get all test temps same group by test id
     *
     * @param string $email
     * @param int $testId
     * @return collection
     */
    public static function getAlltestTempsByTestId($email, $testId, $exceptId = null)
    {
        $groupTbl = LangGroup::getTableName();
        $collect = self::select('temp.*', 'group.lang_code', 'group.group_id')
            ->from(self::getTableName() . ' as temp')
            ->join($groupTbl . ' as group', 'group.test_id', '=', 'temp.test_id')
            ->where('temp.employee_email', $email)
            ->whereIn('group.group_id', function ($query) use ($testId, $groupTbl) {
                $query->select('group_id')
                    ->from($groupTbl)
                    ->where('test_id', $testId);
            })
            ->groupBy('temp.test_id', 'temp.employee_email');
        if ($exceptId) {
            $collect->where('temp.test_id', '!=', $exceptId);
        }
        return $collect->get();
    }

    public static function getTemp($email, $testId)
    {
        return self::where('employee_email', $email)
            ->where('test_id', $testId)
            ->first();
    }
    
}
