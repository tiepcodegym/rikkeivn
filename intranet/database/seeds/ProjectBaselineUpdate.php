<?php

use Rikkei\Project\Model\ProjPointBaseline;
use Rikkei\Core\Seeds\CoreSeeder;

class ProjectBaselineUpdate extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProjPointBaseline::updatePointBaselineAll();
    }
}
