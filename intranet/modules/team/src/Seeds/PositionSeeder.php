<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\Role;

class PositionSeeder extends \Rikkei\Core\Seeds\CoreSeeder
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
        $dataDemo = [
            [
                'role' => 'Team Leader',
                'sort_order' => '1',
                'special_flg' => Role::FLAG_POSITION,
            ],
            [
                'role' => 'Sub-Leader',
                'sort_order' => '2',
                'special_flg' => Role::FLAG_POSITION,
            ],
            [
                'role' => 'Member',
                'sort_order' => '3',
                'special_flg' => Role::FLAG_POSITION,
            ],
        ];
        try {
            foreach ($dataDemo as $data) {
                $rolePosition = Role::where('role', $data['role'])->get();
                if (count($rolePosition)) {
                    continue;
                }
                $roles = new Role();
                $roles->setData($data);
                $roles->save();
            }
            $this->insertSeedMigrate();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
