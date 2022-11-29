<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\Model\Employee;


class Opinion extends CoreModel
{
    use SoftDeletes;

    const STATUS_NEW = 1;
    const STATUS_SEEN = 2;

    protected $fillable = ['content', 'employee_id', 'status', 'created_at', 'updated_at'];

    public static function getStatus()
    {
        return [
            self::STATUS_NEW => 'news::view.status_new',
            self::STATUS_SEEN => 'news::view.status_seen',
        ];
    }

    public static function getStatusLabel()
    {
        return [
            self::STATUS_NEW => 'label label-warning',
            self::STATUS_SEEN => 'label label-success',
        ];
    }

    public function employee()
    {
        return self::belongsTo(Employee::class, 'employee_id');
    }

}
