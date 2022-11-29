<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class FormImplements extends CoreModel
{
    use SoftDeletes;

    protected $table = 'wel_form_implements';

    protected $fillable = ['name', 'created_by'];

    public static function getAllItem()
    {
        $result = DB::table('wel_form_implements')->whereNull('deleted_at')->select('id', 'name');
        return $result;
    }

    public static function optionWelImple()
    {
        $options = [];
        foreach(self::all() as $item) {
            $options[] = [
                'value' => $item->id,
                'lable' => $item->name
            ];
        }
        return $options;
    }

}