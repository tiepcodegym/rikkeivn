<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\Employee;

class Organizer extends CoreModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'wel_organizers';

    protected $fillable=['wel_id','name','phone','position','phone_company','company','email_company','note'];
    protected $primaryKey = 'id';
    
    /**
     * get data table employee
     */
    public static function getWelOrganizer() {
        $team_member = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->join('roles', 'team_members.role_id', '=', 'roles.id')
            ->join('employees', 'team_members.employee_id', '=', 'employees.id')
            ->select('team_members.employee_id',
                DB::raw("group_concat(teams.name) as name"),
                DB::raw("group_concat(DISTINCT roles.role) as role"))
            ->groupBy('employees.id');

        return DB::table('employees')
            ->join(DB::raw('(' . $team_member->toSql() . ') as i'), 'employees.id', '=', 'i.employee_id')
            ->where('employees.leave_date',null)
            ->select('employees.name as empName',
                    'employees.mobile_phone as empPhone',
                    'i.name',
                    'i.role',
                    'employees.email as email',
                    'employees.id as emp_id')
            ->groupBy('employees.id');
    }

    /**
     * update  wel organizers
     */
    public static function updateOrganizers($data) {
        $checkData = self::where('wel_id',$data['wel_id']);
        DB::beginTransaction();
        try {
            if (count($checkData->get()) > 0) {
                $checkData->update(['name'=>$data['name'],'phone'=>$data['phone'],
                    'position'=>$data['position'],'email_company'=>$data['email_company'],
                    'company'=>$data['company'],'note'=>$data['note']]);
            } else {
                self::insert($data);
            }
            DB::commit();
            return true;
        } catch(Exception $ex) {
            DB::rollback();
            throw $ex;
            return false;
        }
    }
}
