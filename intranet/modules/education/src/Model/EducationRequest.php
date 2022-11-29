<?php
namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Lang;

class EducationRequest extends CoreModel
{
    const SCOPE_COMPANY = 1;
    const SCOPE_BRANCH= 2;
    const SCOPE_DIVISION = 3;

    const STATUS_NEW = 1;
    const STATUS_CLOSED = 1;
    const STATUS_PENDING = 2;
    const STATUS_REQUESTING = 3;
    const STATUS_OPENING = 4;
    const STATUS_REJECT= 5;

    const MIN_DATE = '1970-01-01';
    const MAX_DATE = '9999-12-31';
    /**
     *  store this object
     * @var object
     */
    protected static $instance;

    protected $table = 'education_requests';
    protected $fillable = [
        'id', 'employee_id', 'type_id', 'course_id', 'scope_total', 'teacher_id', 'assign_id', 'title', 'description', 'start_date', 'status'
    ];
    public $timestamps = true;

    public function tags()
    {
        return $this->belongsToMany(EducationTag::class, 'education_request_tags', 'education_request_id', 'tag_id');
    }

    public function scopes()
    {
        return $this->belongsToMany(Team::class, 'education_request_scopes', 'education_request_id', 'education_scope_id');
    }

    public function type()
    {
        return $this->belongsTo(EducationType::class, 'type_id', 'id');
    }

    public function objects()
    {
        return $this->hasMany(EducationRequestObject::class, 'education_request_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function teams()
    {
        return $this->hasMany(TeamMember::class, 'employee_id', 'employee_id');
    }

    public function course()
    {
        return $this->belongsTo(EducationCourse::class, 'course_id', 'id');
    }

    public function reason()
    {
        return $this->hasMany(EducationRequestHistory::class, 'education_request_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id', 'id');
    }

    public function assigned()
    {
        return $this->belongsTo(Employee::class, 'assign_id', 'id');
    }

    /**
     * get education status
     * @return array
     */
    public static function getStatus()
    {
        return [
            self::STATUS_CLOSED => Lang::get('education::view.Education.status.Closed'),
            self::STATUS_PENDING => Lang::get('education::view.Education.status.Pending'),
            self::STATUS_REQUESTING => Lang::get('education::view.Education.status.Requesting'),
            self::STATUS_OPENING => Lang::get('education::view.Education.status.Opening'),
            self::STATUS_REJECT => Lang::get('education::view.Education.status.Reject'),
        ];
    }

    /**
     * get education status pending and reject
     * @return array
     */
    public function getStatusNotAllow()
    {
        return [
            self::STATUS_PENDING => Lang::get('education::view.Education.status.Pending'),
            self::STATUS_REJECT => Lang::get('education::view.Education.status.Reject'),
        ];
    }

    /**
     * get education scope
     * @return array
     */
    public function getScopeTotal() {
        return [
            self::SCOPE_COMPANY => Lang::get('education::view.Education.scope.Company'),
            self::SCOPE_BRANCH => Lang::get('education::view.Education.scope.Branch'),
            self::SCOPE_DIVISION => Lang::get('education::view.Education.scope.Division'),
        ];
    }

    /**
     * get instance
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
