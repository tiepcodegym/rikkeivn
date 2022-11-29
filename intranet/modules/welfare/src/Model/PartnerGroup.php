<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerGroup extends CoreModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'partner_types';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function partnerGroup()
    {
        return $this->hasMany(PartnerGroup::class, 'partner_type_id', 'id');
    }
}
