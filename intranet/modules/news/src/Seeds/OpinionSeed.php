<?php

namespace Rikkei\News\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\News\Model\BlogMeta;
use Rikkei\News\Model\Opinion;

class OpinionSeed extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }

        Opinion::where([
            ['status', 0]
        ])->update(['status' => Opinion::STATUS_NEW]);

        $this->insertSeedMigrate();
    }
}
