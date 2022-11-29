<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;

class CategoryMeta extends CoreModel
{
    protected $table = 'ntest_categories_meta';
    protected $fillable = ['name', 'lang_code', 'cate_id'];

    public $timestamps = false;

}
