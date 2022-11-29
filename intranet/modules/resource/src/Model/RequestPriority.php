<?php
namespace Rikkei\Resource\Model;

use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestPriority extends CoreModel
{
    protected $table = 'request_priority';

    use SoftDeletes;

    protected $fillable = ['name', 'name_en', 'name_jp'];

    const PRIORITY_URGENT = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_UNDEFINED = 3;
    const STATE_ACTIVE = 1;

    public static function getPriorityOption() {
        return self::where('state', self::STATE_ACTIVE)->select('*')->get();
    }

    public static function getNameLang($priority, $lang) {
        if (isset($priority)) {
            switch ($lang) {
                case 'vi':
                    return $priority->name;
                    break;
                case 'en':
                    return $priority->name_en;
                    break;
                case 'jp':
                    return $priority->name_jp;
                    break;
                default:
                    return $priority->name;
            }
        }
        return false;
    }

    public static function getPriorityNameById($id)
    {
        return self::where('id', $id)->select('name', 'name_en', 'name_jp')->first();
    }
}
