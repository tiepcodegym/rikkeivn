<?php

namespace Rikkei\Assets\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;

class RequestAssetTeam extends CoreModel
{
    protected $table = 'request_asset_teams';
    public $timestamps = false;
}
