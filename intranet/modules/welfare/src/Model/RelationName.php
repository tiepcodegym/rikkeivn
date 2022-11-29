<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Welfare\Model\WelAttachFee;

class RelationName extends CoreModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'relation_names';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'created_by',
    ];

    /**
     *
     * @param string $name
     * @return RelationName $relation
     */
    public static function getByNameWithTrashed($name)
    {
        return self::where('name', $name)
                ->withTrashed()
                ->first();
    }

    public static function listStringRelation()
    {
        $relation = self::select( DB::raw('group_concat(name) as name'))->first();
        if ($relation) {
            return $relation->name;
        }
        return $relation;
    }

    /**
     * Get name Relation by Id
     *
     * @param int $id
     * @return string
     */
    public static function getNameById($id)
    {
        $name = self::find($id);
        if ($name) {
            return $name->name;
        }
        return $name;
    }

    /**
     * Get name Relation by list Id
     *
     * @param string $list
     * @return string
     */
    public static function listNameByListId($list)
    {
        $arrayId  = explode(',', $list);

        $relation = self::select(DB::raw('group_concat(DISTINCT name separator ", ") as name'))
            ->whereIn('id', $arrayId)
            ->first();

        if ($relation) {
            return $relation->name;
        }
        return $relation;
    }

    /**
     * get id by name relative
     *
     * @param string $name
     * @return array $listId
     */
    public static function getIdByName($name)
    {
        $list   = self::where('name', 'like', '%' . $name . '%')->select('id')->get();
        $listId = [];
        foreach ($list as $value) {
            array_push($listId, $value->id);
        }
        return $listId;
    }

    /**
     * get list name relation by wel_id and id fee mode
     *
     * @param int $wel_id
     * @param int $fee
     * @return boolean
     */
    public static function getListRelation($wel_id, $fee)
    {
        $listRelation = WelAttachFee::where('wel_id', $wel_id)->first();
        switch ($fee) {
            case WelAttachFee::Fee_0 :
                return $listRelation->fee_free_relative;
                break;
            case WelAttachFee::Fee_50 :
                return $listRelation->fee50_relative;
                break;
            case WelAttachFee::Fee_100 :
                return $listRelation->fee100_relative;
                break;

            default :
                return null;
                break;
        }
    }

}
