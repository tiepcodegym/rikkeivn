<?php

namespace Rikkei\Proposed\Model;

use DB;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Proposed\Model\ProposedCategory;
use Rikkei\Team\View\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\CookieCore;

class Proposed extends CoreModel
{
    use SoftDeletes;

    protected $table = 'proposes';

    protected $fillable = [
        'status',
        'answer_content',
        'updated_by',
        'level',
        'feedback',
        'created_at_answer',
    ];

    const STATUS_ = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_NOT_ACTIVE = 3;

    const NO_RESPONSE_YET = 1;
    const RESPONDED = 2;
    const NO_ANSWER = 3;

    const STATUS_LEVEL_ = 1;
    const RECORD = 2;
    const USEFUL = 3;
    const VERRY_HELPFUL = 4;

    const PROPOSED_POINT_ID = 6;

    const PROPOSE_5_POINT = 5;
    const PROPOSE_10_POINT = 10;
    const PROPOSE_30_POINT = 30;
    const PROPOSE_20_POINT = 20;

    /**
     * employe create
     */
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * employee write answer
     */
    public function employeesWAnswers()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    /**
     * catgegory
     */
    public function categories()
    {
        return $this->belongsTo(ProposedCategory::class, 'cat_id');
    }

    /**
     * get list proposed by team
     * @param  [int|null] $id
     * @return [type]
     */
    public function index($id = null)
    {
        $tblPro = static::getTableName();
        $tblProCate = ProposedCategory::getTableName();
        $tblEmp = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();

        $collections = static::select(
            "{$tblPro}.id",
            "{$tblPro}.title",
            "{$tblPro}.proposed_content",
            "empCreate.name as nameEmpCreate", //name employee create proposed
            "empWriteAnswer.name as nameEmpWAnswer", // name employee write answer
            "{$tblPro}.status",
            "{$tblPro}.feedback",
            "{$tblPro}.level",
            "{$tblPro}.created_at"
        )
        ->leftJoin("{$tblEmp} as empCreate", 'empCreate.id', '=', "{$tblPro}.created_by")
        ->leftJoin("{$tblEmp} as empWriteAnswer", 'empWriteAnswer.id', '=', "{$tblPro}.updated_by") // người viết câu trả lời
        ->leftJoin("{$tblProCate} as proCat", 'proCat.id', '=', "{$tblPro}.cat_id")
        ->whereNull("{$tblPro}.deleted_at");
        if ($id) {
            $arrId = TeamList::getTeamChildIds($id);
            $collections->leftJoin("$tblTeamMember", "$tblTeamMember.employee_id", "=", "empCreate.id")
            ->join("$tblTeam", "$tblTeam.id", "=", "$tblTeamMember.team_id")
            ->whereIn("{$tblTeam}.id", $arrId);
        }
        $collections->orderBy(DB::raw("CASE WHEN empWriteAnswer.name IS NULL THEN 1 ELSE 2 END"), "ASC")
            ->orderBy("{$tblPro}.feedback")
            ->orderBy("{$tblPro}.created_at", "DESC")
            ->groupBy("{$tblPro}.id");

        $url = app('request')->url() . '/';
        $pagerOld = Config::getPagerData(null, ['limit' => Constant::PAGINATE_DEFAULT]);
        if (isset($_COOKIE[md5('filter_pager.' . $url)])) {
            CookieCore::setRaw($url, CookieCore::getRaw('filter_pager.' . $url));
            $pager = Config::getPagerData(null, ['limit' => Constant::PAGINATE_DEFAULT]);
        } elseif (!isset($_COOKIE[md5($url)])) {
            $pager = Config::getPagerData(null, ['limit' => Constant::PAGINATE_DEFAULT]);
            CookieCore::setRaw($url, $pager);
        } else {
            $pager = CookieCore::getRaw($url);
        }
        $pager['page'] = $pagerOld['page'];
        self::filterGrid($collections, [], null, 'LIKE');
        self::pagerCollection($collections, $pager['limit'], $pager['page']);
        return $collections;
    }
    /**
     * trạng thái
     * hiển thị: list bên ngoài áp
     * đóng: không hiển thị ra bên ngoài áp
     * @return [type] [description]
     */
    public static function getStatus()
    {
        return [
            static::STATUS_ => Lang::get('proposed::view.Status_'),
            static::STATUS_ACTIVE => Lang::get('proposed::view.Display'),
            static::STATUS_NOT_ACTIVE => Lang::get('proposed::view.Not display'),
        ];
    }

    /**
     * trạng thái phản hồi
     *
     * @return [array]
     */
    public static function getFeedback()
    {
        return [
            static::NO_RESPONSE_YET => Lang::get('proposed::view.No response yet'),
            static::RESPONDED => Lang::get('proposed::view.Responded'),
            static::NO_ANSWER => Lang::get('proposed::view.No answer'),
        ];
    }

    /**
     * trạng thái ghi nhận
     *
     * @return [array]
     */
    public static function getLevelRecognition()
    {
        return [
            static::STATUS_LEVEL_ => Lang::get('proposed::view.Status_'),
            static::RECORD => Lang::get('proposed::view.Record'),
            static::USEFUL => Lang::get('proposed::view.Useful'),
            static::VERRY_HELPFUL => Lang::get('proposed::view.Very helpful'),
        ];
    }
}
