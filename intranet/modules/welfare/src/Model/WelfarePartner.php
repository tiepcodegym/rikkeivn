<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;

class WelfarePartner extends CoreModel
{
    /**
     * @var string
     */
    protected $table = 'wel_partners';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'wel_id',
        'rep_gender',
        'rep_name',
        'rep_position',
        'rep_phone',
        'rep_phone_company',
        'email',
        'fee_return',
        'note',
    ];

    public $timestamps = false;

    /**
     *
     * @param int $welId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWelPartner($welId)
    {
        return self::select('wel_partners.*', 'partners.address', 'partners.website')
            ->join('partners', 'wel_partners.partner_id', '=', 'partners.id')
            ->where('wel_id', $welId)
            ->first();
    }
}
