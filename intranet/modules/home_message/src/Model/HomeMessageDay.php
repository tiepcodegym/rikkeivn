<?php


namespace Rikkei\HomeMessage\Model;


use Illuminate\Database\Eloquent\Model;

class HomeMessageDay extends Model
{
    const  IGNORE = 1;
    const  NOT_IGNORE = 0;
    protected $table = 'home_message_day';
    public $timestamps = false;

    protected $fillable = [
        'home_message_id',
        'type',
        'permanent_day',
        'is_sun',
        'is_mon',
        'is_tues',
        'is_wed',
        'is_thur',
        'is_fri',
        'is_sar',
    ];

    public function homeMessage()
    {
        return $this->belongsTo(HomeMessage::class);
    }
}
