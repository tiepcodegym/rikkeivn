<?php

namespace Rikkei\Welfare\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

use Illuminate\Database\Eloquent\SoftDeletes;
class PurposeEvent extends CoreModel
{
    use SoftDeletes;

    protected $table = 'wel_purposes';
    protected $fillable = ['name', 'created_by'];

    public static function getAllItem()
    {
        $result = DB::table('wel_purposes')->select('name', 'id', 'created_at')->where('deleted_at', null);
        return $result;
    }

}