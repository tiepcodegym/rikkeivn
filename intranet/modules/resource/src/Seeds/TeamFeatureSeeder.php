<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\Model\TeamFeature;

class TeamFeatureSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        
        $dataDemo = [
            [
                'name' => 'Web'
            ],
            [
                'name' => 'Web (tách)'
            ],
            [
                'name' => 'iOS',
            ],
            [
                'name' => 'Android'
            ],
            [
                'name' => 'Mobile (tách)'
            ],
            [
                'name' => 'Game'
            ],
            [
                'name' => 'Finance'
            ],
            [
                'name' => 'Finance (tách)'
            ],
            [
                'name' => 'QA'
            ],
            [
                'name' => 'Rikkei - Danang'
            ],
            [
                'name' => 'Sales'
            ],
            [
                'name' => 'Production'
            ],
            [
                'name' => 'BOD + HCTH'
            ]
        ];
        
        foreach ($dataDemo as $order => $data) {
            $data['sort_order'] = $order;
            TeamFeature::create($data);
        }
        
        $this->insertSeedMigrate();
        
    }
}
