<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class EmployeeAttach extends CoreModel
{
    protected $table = 'employee_attach';
    const KEY_CACHE = 'esmployee_attach';
    protected $fillable = [
        'title', 'note', 'required'
    ];

    public function attachFiles()
    {
        return $this->hasMany(EmployeeAttachFile::class, 'attach_id', 'id');
    }

    /**
     * get collection by employeeId
     *
     * @param int $employeeId
     * @return $collection
     */
    public static function getAllAttach($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'title', 'required', 't_eaf.path as is_file'])
            ->leftJoin('employee_attach_files as t_eaf', 't_eaf.attach_id', '=', 'employee_attach.id')
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir'])
            ->groupBy('employee_attach.id');
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
