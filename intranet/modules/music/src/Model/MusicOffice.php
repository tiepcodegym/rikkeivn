<?php

namespace Rikkei\Music\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\Form as FormCore;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\DB;
use Rikkei\Music\Model\MusicOfficeTime;
use Illuminate\Database\Eloquent\SoftDeletes;


class MusicOffice extends CoreModel
{
     use SoftDeletes;
    
    protected $table = 'music_offices';
    const ENABLE_STATUS = 1;
    const DISABLE_STATUS = 0;
    
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'status',
        'sort_order',
        'employee_noti',
        'created_by',
    ];

    /**
    * get list of music offices
    */ 
    public static function getGridData()
    {
        $tableOffices = self::getTableName();
        $pager = TeamConfig::getPagerData();
        $collection = self::select($tableOffices.'.id', $tableOffices.'.name', $tableOffices.'.status', $tableOffices.'.sort_order', $tableOffices.'.employee_noti', $tableOffices.'.created_by', $tableOffices.'.created_at','employees.email');
        if (FormCore::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        $filterName = FormCore::getFilterData('spec', 'name');
        $collection->leftJoin('employees', $tableOffices.'.employee_noti', '=', 'employees.id');
        self::filterGrid($collection,[],null,'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
    * delete music office
    */ 
    public static function delOffice($officeId)
    {
        self::where('id', '=', $officeId)->delete();
    }

    /**
    * save music office
    */ 
    public function save(array $options = [])
    {
        DB::beginTransaction();
        try {
            $this->created_by = Permission::getInstance()->getEmployee()->id;
            $result = parent::save($options);
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }


    /**
     * Get all office
     * 
     * @return array
     */
    public static function getOffices()
    {
        return self::select('id','name')
                ->where('status','=',self::ENABLE_STATUS)
                ->groupBy('id')
                ->orderBy('sort_order','ASC')
                ->get();
    }

    /**
    * get all status
    */ 
    public static function getAllStatus() 
    {
        return [
            self::DISABLE_STATUS => 'Disable',
            self::ENABLE_STATUS => 'Enable',
        ];
    }

    /**
    * get all time of office
    */ 
    public function getAllTime()
    {
        return MusicOfficeTime::getTimeOfOffice($this->id);
    }

    /**
    * save time for office
    */ 
    public function saveTime(array $options = [])
    {
        MusicOfficeTime::saveTime($this->id, $options);
    }

    /**
    * get office follow id
    */ 
    public static function getOfficeFollowId($officeId)
    {
        $office = self::leftJoin('employees', 'music_offices.employee_noti', '=','employees.id')->where('music_offices.id','=',$officeId)->select('music_offices.id','music_offices.name','music_offices.status','music_offices.sort_order','music_offices.employee_noti','employees.nickname','employees.email')->first();
        return $office;
    }

    /**
    * get all id of employee noti
    */
    public static function getAllIdNoti()
    {
        $empNoti = array();
        $employees = Employee::get();
        foreach($employees as $employee) {
            $empNoti[] = $employee->id;
        }
        return $empNoti;
    }
    
    /**
     * Check employee is admin office
     * 
     * @param int $office_id
     * @return boolean
     */
    public static function isAdmin($officeId)
    {
        $isAdmin = false;
        $office  = self::where('music_offices.id', '=', $officeId)
                ->Where('music_offices.status', '=', self::ENABLE_STATUS)
                ->first();
        if (!empty($office->employee_noti) && $office->employee_noti == Permission::getInstance()->getEmployee()->id) {
            $isAdmin = true;
        }
        return $isAdmin;
    }
    
    /**
     * Get office by id
     * 
     * @param int $officeId
     * @return type
     */
    public static function getOffice($officeId)
    {
        return self::where('music_offices.id','=',$officeId)
                ->Where('music_offices.status','=', self::ENABLE_STATUS)
                ->first();
    }

    /**
    * get count office by name
    */
    public static function countOfficebyName($officeName, $officeId = null)
    {
        if($officeId != null){
            return self::where('name', '=', $officeName)->where('id','!=',$officeId)->count();
        }
        return self::where('name', '=', $officeName)->count();
    }

}
