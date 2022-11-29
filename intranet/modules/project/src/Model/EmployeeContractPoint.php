<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Lang;

class EmployeeContractPoint extends ProjectWOBase
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'employee_contract_point';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_type', 'point'];

}
