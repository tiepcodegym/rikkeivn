<?php

namespace Rikkei\TestOld\Models;

use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    protected $table = 'teams';
    protected $fillable = ['name', 'parent_id'];
}
