<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\View\Acl;

class PermissionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  'permission.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            if (isset($dataDemo) && $dataDemo) {
                $this->createPermission($dataDemo);
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
    protected function createPermission($data)
    {
        //get acl
        $acl = Acl::getAclList();
        if (! count($acl)) {
            return;
        }
        $aclDataId = [];
        $aclDataIdSetting = [];
        foreach ($acl as $aclKey => $aclValue) {
            if (isset($aclValue['child']) && count($aclValue['child'])) {
                //check permission setting: role, team, 
                if (strtolower($aclValue['description']) == 'setting') {
                    $checkSetting = true;
                } else {
                    $checkSetting = false;
                }
                foreach ($aclValue['child'] as $aclItemKey => $aclItem) {
                    $aclDataId[] = $aclItemKey;
                    if ($checkSetting) {
                        $aclDataIdSetting[] = $aclItemKey;
                    }
                }
            }
        }
        if (! $aclDataId || ! count($aclDataId)) {
            return;
        }
        //insert permission
        foreach ($data as $item) {
            $team = Team::where('name', $item['team'])->first();
            $role = Role::where('role', $item['role'])
                ->where('special_flg', Role::FLAG_POSITION)->first();
            if (! $team || ! $role) {
                continue;
            }
            $scope = $item['scope'];
            $itemData = [];
            foreach ($aclDataId as $aclId) {
                //check permission setting: nhan su, bod
                if (strtolower($item['team']) != 'bod' &&
                    strtolower($item['team']) != 'nhân sự' &&
                    in_array($aclId, $aclDataIdSetting)) {
                    $scope = \Rikkei\Team\Model\Permission::SCOPE_NONE;
                }
                $itemData[] = [
                    'team_id' => $team->id,
                    'role_id' => $role->id,
                    'action_id' => $aclId,
                    'scope' => $scope
                ];
            }
            Permission::saveRule($itemData, $team->id);
        }
    }
}
