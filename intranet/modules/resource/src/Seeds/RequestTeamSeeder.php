<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\ResourceRequest;

class RequestTeamSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     * Transfer data to new table request team
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $requests = ResourceRequest::all();
        $data = [];
        if (count($requests)) {
            foreach ($requests as $item) {
                if ($item->team_id) {
                    $data[] = [
                        'request_id' => $item->id,
                        'team_id' => $item->team_id,
                        'position_apply' => $item->role,
                        'number_resource' => $item->number_resource
                    ];
                }
                
            }
        }
        DB::beginTransaction();
        try {
            RequestTeam::insert($data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
        
    }
}
