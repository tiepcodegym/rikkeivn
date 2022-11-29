<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Storage;

/**
 * Class WelfareFile
 *
 * @package Rikkei\Welfare\Models
 * @property int $id
 * @property int $wel_id
 * @property string $file
 * @property string $fileUrl
 * @property datetime created_at
 */
class WelfareFile extends CoreModel
{
    const ACCESS_FILE = 'public/welfare';

    /**
     * @var string
     */
    protected $table = 'wel_files';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'wel_id',
        'files',
        'created_at',
    ];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'wel_id', 'id');
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public static function getFileByEvent($id)
    {
        return static::Where('wel_id', $id)->get();
    }

    /**
     * @return mixed
     */
    public function getFileUrlAttribute()
    {
        if ($this->attributes['files']) {
            return url(Storage::url(self::ACCESS_FILE.'/'.$this->attributes['files']));

        }
        return null;
    }
}

