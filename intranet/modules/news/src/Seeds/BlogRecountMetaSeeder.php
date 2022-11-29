<?php

namespace Rikkei\News\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\News\Model\BlogMeta;

class BlogRecountMetaSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BlogMeta::reCount();
    }
}
