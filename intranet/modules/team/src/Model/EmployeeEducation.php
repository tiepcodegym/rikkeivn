<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeEducation extends CoreModel
{
    protected $table = 'employee_educations';
    protected $fillable = [
        'school', 'country', 'province', 'start_at', 'end_at', 'faculty', 'majors',
        'quality', 'type', 'degree', 'is_graduated', 'awarded_date', 'note'
    ];

    /**
     * get all education of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getAllEducation($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'school_id', 'start_at', 'end_at',
            'major_id', 'quality', 'degree'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * overwrite save method
     *
     * @param array $options
     */
    /*public function save(array $options = array())
    {
        if (!isset($this->original['school']) ||
            (isset($this->original['school']) &&
            isset($this->attributes['school']) &&
            $this->school &&
            $this->attributes['school'] !== $this->original['school'])
        ) {
            School::checkAndSaveFromEducation($this->school, $this->country, $this->province);
        }
        return parent::save($options);
    }*/
}
