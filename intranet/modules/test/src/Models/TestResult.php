<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\Models\Question;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form;
use Rikkei\Team\View\Config;
use Rikkei\Test\View\ViewTest;

class TestResult extends CoreModel
{
    protected $table = 'ntest_result_detail';
    protected $fillable = ['test_id', 'test_result_id', 'question_id', 'parent_id', 'answer_id', 'answer_content', 'is_correct'];
    public $colsSync = ['is_correct', 'created_at', 'updated_at'];

    /**
     * relationship childs
     * @return type
     */
    public function childs() {
        return $this->hasMany('\Rikkei\Test\Models\TestResult', 'parent_id');
    }
    
    /**
     * get parent result that belongs to
     * @return type
     */
    public function rsParent()
    {
        return $this->belongsTo('\Rikkei\Test\Models\TestResult', 'parent_id');
    }
    
    /**
     * relationship questions
     * @return type
     */
    public function questions() {
        return $this->belongsTo('\Rikkei\Test\Models\Question', 'question_id');
    }
    
    /**
     * get total answer
     * @param type $email
     * @param type $test_id
     * @return type
     */
    public static function getTotalAnswer($resultId) {
        return self::where('test_result_id', $resultId)
                ->whereNull('parent_id')
                ->get()->count();
    }
    
    /**
     * get total answer correct
     * @param type $email
     * @param type $test_id
     * @return type
     */
    public static function getTotalAnswerCorrect($resultId) {
        return self::where('test_result_id', $resultId)
                ->whereNull('parent_id')
                ->where('is_correct', 1)
                ->get()->count();
    }
    
    /**
     * get test result by email
     * @param type $email
     * @param type $test_id
     * @return type
     */
    public static function getByEmail($email, $test_id) {
        $qtTbl = Question::getTableName();
        $trsTbl = self::getTableName();
        return self::rightJoin($qtTbl.' as qt', $trsTbl.'.question_id', '=', 'qt.id')
                ->with('childs')
                ->with(['questions' => function ($query) {
                    $query->with('answers', 'childs');
                }])
                ->where('employee_email', $email)
                ->where('test_id', $test_id)
                ->whereNull($trsTbl.'.parent_id')
                ->select($trsTbl.'.id as trs_id', $trsTbl.'.question_id', $trsTbl.'.answer_id', $trsTbl.'.answer_content', $trsTbl.'.is_correct',
                        'qt.id', 'qt.type')
                ->groupBy('qt.id')
                ->get();
    }

    /**
     * collect num correct, not correct of questions in test
     * @param type $test
     * @param array type $data
     * @param null int $status
     * @return type
     */
    public static function questionAnalytic($test, $data = [], $status = null)
    {
        if (!is_object($test)) {
            $test = Test::findOrFail($test);
        }
        $testerType = isset($data['tester_type']) ? $data['tester_type'] : ViewTest::TESTER_PRIVATE;
        $tblDetail = self::getTableName();
        $tblQuestion = Question::getTableName();
        $urlFilter = request()->url() . '/analytic_tab';
        $pager = Config::getPagerData($urlFilter);
        
        $collection = $test->questions(false)
                ->select($tblQuestion.'.id', $tblQuestion.'.status', $tblQuestion.'.content', $tblQuestion.'.is_editor',
                        DB::raw('SUM(CASE WHEN detail.is_correct = 1 THEN 1 ELSE 0 END) as sum_correct'),
                        DB::raw('SUM(CASE WHEN detail.is_correct = 0 THEN 1 ELSE 0 END) as sum_not_correct'),
                        DB::raw('COUNT(detail.id) as total_answer'))
                ->leftJoin(
                    DB::raw('(SELECT detail2.* '
                        . 'FROM ' . $tblDetail . ' AS detail2 '
                        . 'INNER JOIN ' . Result::getTableName() . ' AS result '
                        . 'ON detail2.test_result_id = result.id '
                        . 'AND result.tester_type = ' . $testerType . ') as detail'),
                    function ($join) use ($tblQuestion) {
                        $join->on($tblQuestion . '.id', '=', 'detail.question_id')
                            ->whereNull('detail.parent_id');
                    }
                )
                ->groupBy($tblQuestion.'.id');
        $status = !$status ? Form::getFilterData('status', null, $urlFilter) : $status;
        if ($status) {
            $collection->where($tblQuestion.'.status', $status);
        }
        
        if (Form::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        }
        
        return $collection->get();
    }
    
}
