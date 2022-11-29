<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\CoreConfigData;
use DB;

class SetTeamEmployeeBusinessTripSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }
        DB::beginTransaction();
        try {
            $collection = DB::select("select
                r.id,
                r.creator_id,
                e.employee_id,
                e.start_at,
                e.end_at,
                eth.start_at as start_h,
                eth.end_at as end_h,
                teams.id as team_id,
                group_concat(distinct teams.id separator ', ') as gr_team_id,
                group_concat(distinct teams.name separator ', ') as gr_team_name
            from business_trip_registers as r
            inner join business_trip_employees as e on r.id = e.register_id
            inner join employee_team_history as eth on eth.employee_id = e.employee_id
            inner join teams on teams.id = eth.team_id
            where r.deleted_at is null
                and eth.deleted_at is null
                and (
                    (eth.start_at is not null 
                        and e.end_at >= eth.start_at
                        and ((eth.end_at is not null and e.start_at <= eth.end_at) or eth.end_at is null ))
                    or eth.start_at is null
                )
            group by r.id, e.employee_id
            order by e.employee_id");
            foreach ($collection as $item) {
                DB::table('business_trip_employees')
                    ->where('register_id', $item->id)
                    ->where('employee_id', $item->employee_id)
                    ->update(['team_id' => $item->team_id]);
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
