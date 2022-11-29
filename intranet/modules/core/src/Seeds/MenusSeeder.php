<?php
namespace Rikkei\Core\Seeds;

use Rikkei\Core\Model\Menu;

class MenusSeeder extends CoreSeeder
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
                'name' => 'Rikkei Intranet',
                'state' => Menu::FLAG_MAIN
            ],
            [
                'name' => 'Setting',
                'state' => Menu::FLAG_SETTING
            ]
        ];
        try {
            foreach ($dataDemo as $data) {
                if (count(Menu::where('state', $data['state'])->get())) {
                    continue;
                }
                $menu = new Menu();
                $menu->setData($data);
                $menu->save();
            }
            $this->insertSeedMigrate();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
