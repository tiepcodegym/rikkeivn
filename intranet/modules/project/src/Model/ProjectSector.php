<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class ProjectSector extends CoreModel
{
    protected $table = 'projs_sector';
    protected $fillable = ['sub_sector', 'is_other_type'];

    public static function getSubSectorById($sectorId)
    {
        $sector = self::select('sub_sector')->where('id', $sectorId)->first();
        return $sector->sub_sector;
    }
}