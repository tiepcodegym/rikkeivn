<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use Carbon\Carbon;

class Partners extends CoreModel
{
    use SoftDeletes;

    /*
     * flag value gender
     */
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    /**
     * @var string
     */
    protected $table = 'partners';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'partner_type_id',
        'address',
        'phone',
        'fax',
        'email',
        'website',
        'tax_code',
        'bank_account',
        'bank_account_address',
        'note',
        'rep_name',
        'rep_card_id',
        'rep_position',
        'rep_card_id_date',
        'rep_gender',
        'rep_card_id_address',
        'rep_email',
        'rep_phone',
        'rep_phone_home',
        'rep_phone_compay',
        'rep_address',
    ];

    public static function optionGender()
    {
        return [
            self::GENDER_MALE => Lang::get('team::view.Male'),
            self::GENDER_FEMALE => Lang::get('team::view.Female'),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(Partners::class, 'partner_type_id', 'id');
    }

    public function getRepCardIdDateAttribute()
    {
        if ($this->attributes['rep_card_id_date'] != null) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['rep_card_id_date'])->format('Y-m-d');
        }
        return null;
    }

}
