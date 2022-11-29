<?php

namespace Rikkei\TestOld\Models;

use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    protected $table = 'md_test_password';
    protected $fillable = ['password'];
    public $timestamps = false;
}
