<?php

namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupEvent extends CoreModel
{

    use SoftDeletes;

    protected $table = 'welfare_groups';
    protected $fillable = ['name', 'created_by'];

    public static function getAllItem()
    {
        $result = DB::table('welfare_groups')->select('name', 'id', 'created_at')->where('deleted_at', null);
        return $result;
    }

}
