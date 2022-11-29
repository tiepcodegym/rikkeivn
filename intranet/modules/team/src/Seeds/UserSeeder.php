<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;

class UserSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $userDataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  'user.php';
        if (! file_exists($userDataFilePath)) {
            return;
        }
        $dataDemo = require $userDataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            if (isset($dataDemo) && $dataDemo) {
                //$this->createEmployee($dataDemo);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * create employee demo
     * 
     * @param array $data
     */
    protected function createEmployee($data)
    {
        foreach ($data as $item) {
            $employee = Employee::where('email', $item['email'])->first();
            //add employee
            if (! $employee) {
                $employee = new Employee();
                $employee->email = $item['email'];
                $employee->nickname = preg_replace('/@.*$/', '', $item['email']);
                $employee->save();
            }
            //add team and position
            $team = Team::where('name', $item['team'])->first();
            $role = Role::where('role', $item['role'])->where('special_flg', Role::FLAG_POSITION)->first();
            if (! $team || ! $role) {
                continue;
            }
            $employee->saveTeamPosition([
                [
                    'team' => $team->id,
                    'position' => $role->id,
                ]
            ]);
        }
    }
}
