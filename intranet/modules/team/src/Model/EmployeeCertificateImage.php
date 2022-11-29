<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;

class EmployeeCertificateImage extends CoreModel
{
    protected $table = 'employee_certies_image';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'image'
    ];
}
