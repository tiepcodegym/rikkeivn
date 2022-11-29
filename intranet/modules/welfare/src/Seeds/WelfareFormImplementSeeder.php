<?php

namespace Rikkei\Welfare\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Welfare\Model\FormImplements;

class WelfareFormImplementSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('WelfareFormImplementSeeder-v4')) {
            return true;
        }
        try {
            foreach ($this->data() as $value) {
                if (count(FormImplements::where('name', $value)->get())) {
                    continue;
                }
                $relation = new FormImplements();
                $relation->name = $value;
                $relation->save();
            }
            $this->insertSeedMigrate();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function data()
    {
        return [
            1   => 'Bên Ngoài',
            2   => 'Nội Bộ',
        ];
    }
}