<?php

namespace Rikkei\News\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\News\Model\Post;

class ChangeConstantStatusNewsSeed extends CoreSeeder
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
        Post::where('status', 1)->update(['status' => Post::STATUS_DISABLE]);
        Post::where('status', 2)->update(['status' => Post::STATUS_ENABLE]);
        $this->insertSeedMigrate();
    }
}
