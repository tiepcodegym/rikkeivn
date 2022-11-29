<?php

namespace Rikkei\Resource\Model;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;

class ChannelCostLog extends CoreModel
{

    protected $table = 'channel_cost_logs';

    use SoftDeletes;

    protected $fillable = [];

}
