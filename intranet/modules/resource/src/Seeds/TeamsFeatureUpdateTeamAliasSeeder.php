<?php
namespace Rikkei\Resource\Seeds;

use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\TeamFeature;

class TeamsFeatureUpdateTeamAliasSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        
        DB::beginTransaction();
        try {
            $teamList = Team::all();
            $arrTeam = [];
            $arrTeamId = [];
            $replaceTeam = [
                'Mobile' => 'D1',
                'Web' => 'D2',
                'Game' => 'D3',
                'Finance' => 'D5',
            ];
            foreach ($teamList as $team) {
                $arrTeamId[$team->name] = $team->id;
                $arrTeam[] = $team->name;
            }
            $teamFeatureList = TeamFeature::getList();
            foreach ($teamFeatureList as &$teamFeature) {
                if (in_array($teamFeature->name, $arrTeam)) {
                    $teamFeature->team_alias = $arrTeamId[$teamFeature->name];
                    $teamFeature->save();
                }
                if (isset($replaceTeam[$teamFeature->name]) && isset($arrTeamId[$replaceTeam[$teamFeature->name]])) {
                    $teamFeature->team_alias = $arrTeamId[$replaceTeam[$teamFeature->name]];
                    $teamFeature->save();
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $ex.getMessage();
        }
        
    }
}
