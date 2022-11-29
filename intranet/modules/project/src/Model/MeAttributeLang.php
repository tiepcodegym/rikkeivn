<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class MeAttributeLang extends CoreModel
{
    protected $table = 'me_attribute_lang';
    protected $fillable = ['name', 'label', 'description', 'lang_code', 'attr_id'];

    public $timestamps = false;

    public static function getAll()
    {
        return self::orderBy('name', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }
}
