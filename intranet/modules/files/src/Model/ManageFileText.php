<?php

namespace Rikkei\Files\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Rikkei\Team\View\Config;
use Lang;
use Rikkei\Team\View\Permission;
use Auth;
use Rikkei\Core\View\View;

class ManageFileText extends CoreModel
{
    use SoftDeletes;
    protected $table = 'manage_file_text';
    protected $fillable = [];

    /**
     * Type of file text
     */
    const SUBLEADER = 1;
    const LEADER = 2;
    const CVDEN = 1;
    const CVDI = 2;
    const APPROVAL = 1;
    const UNAPPROVAL = 2;
    const PATH_FILE = 'app/public/filemanager/';
    const TYPEFILE_QD = 1;
    const TYPEFILE_CV = 2;
    const TEAMBOD = 1;
    const ROUTE_VIEW_LIST_FILE = 'file::file.index';
    const ROUTE_VIEW_EDIT_FILE = 'file::file.editApproval';

    public $timestamps = true;

    /*Count Type Go */
    public static function countTypeGo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDI)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }

    /*Count Type To*/
    public static function countTypeTo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDEN)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }

    /*get last Id getLastIdTypeTo*/
    public static function getLastIdTypeTo()
    {
        return self::where('type', self::CVDEN)->max('number_to');
    }

    /*get last Id getLastIdTypeGo*/
    public static function getLastIdTypeGo()
    {
        return self::where('type', self::CVDI)->max('number_go');
    }

    /*get info Register Text File*/
    public static function getInformationRegister($id)
    {
        return self::where('id', $id)->first();
    }

    /*
     * get collection to show grid data all
     * @return collection model
     */
    public static function getGridDataAll()
    {
        $pager = Config::getPagerData(null, ['order' => 'quote_text']);
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->select('manage_file_text.code_file', 'employees.name as name_employees',
                    'teams.name as name_teams', 'manage_file_text.type_file',
                    'manage_file_text.type', 'manage_file_text.date_file',
                    'manage_file_text.quote_text', 'manage_file_text.note_text',
                    'manage_file_text.id', 'manage_file_text.status'
                )
            ->orderBy('id', 'desc')
            ->orderBy($pager['order'], $pager['dir']);
        $urlRoute = self::ROUTE_VIEW_LIST_FILE;

        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {

        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
        } else {
            $collection->where(function ($query) {
                $currentUser = Permission::getInstance()->getEmployee();
                $query->where('manage_file_text.created_by', $currentUser->id);
            });
        }

        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * get collection to show grid data
     * @return collection model
     */
    public static function getGridData($type, $status)
    {
        $pager = Config::getPagerData(null, ['order' => 'quote_text']);
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->select('manage_file_text.code_file', 'employees.name as name_employees',
                    'teams.name as name_teams', 'manage_file_text.type_file',
                    'manage_file_text.type', 'manage_file_text.date_file',
                    'manage_file_text.quote_text', 'manage_file_text.note_text',
                    'manage_file_text.id', 'manage_file_text.status'
                )
            ->whereNull('manage_file_text.deleted_at')
            ->orderBy('id', 'desc')
            ->orderBy($pager['order'], $pager['dir']);
            if ($type == self::CVDI && $status == self::APPROVAL) {
                $collection->where('manage_file_text.type', self::CVDI)
                            ->where('manage_file_text.status', self::APPROVAL);
            } elseif ($type == self::CVDI && $status == self::UNAPPROVAL) {
                $collection->where('manage_file_text.type', self::CVDI)
                            ->where('manage_file_text.status', self::UNAPPROVAL);
            } elseif ($type == self::CVDEN && $status == self::APPROVAL) {
                $collection->where('manage_file_text.type', self::CVDEN)
                            ->where('manage_file_text.status', self::APPROVAL);
            } elseif ($type == self::CVDEN && $status == self::UNAPPROVAL) {
                $collection->where('manage_file_text.type', self::CVDEN)
                            ->where('manage_file_text.status', self::UNAPPROVAL);
            } elseif ($type == self::CVDI) {
                 $collection->where('manage_file_text.type', self::CVDI);
            } elseif ($type == self::CVDEN) {
                 $collection->where('manage_file_text.type', self::CVDEN);
            } else {
                //nothing
            }
            $collection->orderBy('id', 'desc');
        $urlRoute = self::ROUTE_VIEW_LIST_FILE;

        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {

        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
        } else {
            $collection->where(function ($query) {
                $currentUser = Permission::getInstance()->getEmployee();
                $query->where('manage_file_text.created_by', $currentUser->id);
            });
        }

        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    /**
     * Option type files list
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            ['id' => self::CVDEN, 'name' => Lang::get('files::view.Công văn đến')],
            ['id' => self::CVDI, 'name' => Lang::get('files::view.Công văn đi')]
        ];
    }

    /**
     * get ceo company
     * @return array
     */
    public static function getCeoCompany()
    {
        return DB::table('team_members')
            ->leftJoin('employees', 'team_members.employee_id', '=', 'employees.id')
            ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
            ->where('team_id', self::TEAMBOD)
            ->whereIn('role_id', [self::SUBLEADER ,self::LEADER])
            ->select('employees.id', 'employees.name', 'employees.position')
            ->get();
    }

    /**
     * Option type status
     * @return array
     */
    public static function getTypeStatus()
    {
        return [
            ['id' => self::APPROVAL, 'name' => Lang::get('files::view.Đã vào sổ')],
            ['id' => self::UNAPPROVAL, 'name' => Lang::get('files::view.Chưa vào sổ')]
        ];
    }

    /*
     * get file by id
     * @param int
     * return obj
     */
    public static function getFileById($id) 
    {
        $currentUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, self::ROUTE_VIEW_EDIT_FILE)) {
            $collection = self::find($id);
        } elseif (Permission::getInstance()->isScopeSelf(null, self::ROUTE_VIEW_EDIT_FILE)) {
            $collection = self::where('created_by', $currentUser->id)->find($id);
            if (!$collection) {
                View::viewErrorPermission();
            }
        } elseif (Permission::getInstance()->isScopeTeam(null, self::ROUTE_VIEW_EDIT_FILE)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection = self::whereIn('created_by', $employeesId)->find($id);
            if (!$collection) {
                View::viewErrorPermission();
            }
        } else {
            View::viewErrorPermission();
        }
        return $collection;
    }

    /*
     * get file by id
     * @param int
     * return array
     */
    public static function getAllFileById($id)
    {
        return DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->where('manage_file_text.id', $id)
            ->select('manage_file_text.code_file', 'employees.name as name_employees',
                    'employees.position as position_employees',
                    'teams.name as name_teams', 'manage_file_text.type_file', 'teams.id as id_teams',
                    'manage_file_text.type', 'manage_file_text.date_file',
                    'manage_file_text.quote_text', 'manage_file_text.note_text',
                    'manage_file_text.id', 'manage_file_text.status', 'manage_file_text.save_file',
                    'manage_file_text.tick', 'manage_file_text.number_go', 'manage_file_text.number_to',
                    'manage_file_text.note_text', 'manage_file_text.file_to', 'manage_file_text.file_from',
                    'manage_file_text.date_released', 'manage_file_text.date_file_send', 'manage_file_text.team_id',
                    'manage_file_text.signer', 'manage_file_text.file_content', 'manage_file_text.content'
                )
            ->first();
    }

    /**
     * check ceo company
     * @return obj
     */
    public static function checkCeoCompany($id)
    {
        return DB::table('employees')->where('id', $id)->select('position')->first();
    }

    /**
     * count status Approval CVDEN
     * @return obj
     */
    public static function countApprovalTo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDEN)
            ->where('status', self::APPROVAL)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }

    /**
     * count status UnApproval CVDEN
     * @return obj
     */
    public static function countUnApprovalTo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDEN)
            ->where('status', self::UNAPPROVAL)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }

    /**
     * count status Approval CVDI
     * @return obj
     */
    public static function countApprovalGo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDI)
            ->where('status', self::APPROVAL)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }

    /**
     * count status UnApproval CVDI
     * @return obj
     */
    public static function countUnApprovalGo()
    {
        $collection = DB::table('manage_file_text')
            ->leftJoin('employees', 'manage_file_text.signer', '=', 'employees.id')
            ->leftJoin('teams', 'manage_file_text.team_id', '=', 'teams.id')
            ->whereNull('manage_file_text.deleted_at')
            ->where('manage_file_text.type', self::CVDI)
            ->where('status', self::UNAPPROVAL)
            ->select('manage_file_text.code_file');

        $urlRoute = self::ROUTE_VIEW_LIST_FILE;
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {
            $collection = $collection->count();
        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = \Rikkei\Team\View\CheckpointPermission::getArrTeamIdByEmployee(Auth::user()->employee_id);
            $employeesId = DB::table('employees')
                            ->join('team_members', 'employees.id', '=', 'team_members.employee_id')
                            ->whereIn('team_members.team_id', $teamsOfEmp)->lists('id');
            $collection->whereIn('manage_file_text.created_by', $employeesId);
            $collection = $collection->count();
        } else {
            $collection->where('manage_file_text.created_by', Auth::user()->employee_id);
            $collection = $collection->count();
        }
        return $collection;
    }
}
