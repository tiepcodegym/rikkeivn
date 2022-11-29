<?php

namespace Rikkei\Welfare\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Welfare\Model\RelationName;

class RelationNameSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('RelationNameSeeder-v2')) {
            return true;
        }
        try {
            foreach ($this->data() as $value) {
                if (count(RelationName::where('name', $value)->get())) {
                    continue;
                }
                $relation = new RelationName();
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
            1   => 'Anh',
            2   => 'Bà',
            3   => 'Bố',
            4   => 'Chị',
            5   => 'Chồng',
            6   => 'Con Gái',
            7   => 'Con Trai',
            8   => 'Em Gái',
            9   => 'Em Trai',
            10  => 'Khác',
        ];
    }
}
