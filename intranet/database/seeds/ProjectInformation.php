<?php

use Rikkei\Project\Model\ProjPointFlat;

class ProjectInformation extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProjPointFlat::flatAllProject();
    }
}
