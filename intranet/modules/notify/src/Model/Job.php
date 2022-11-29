<?php

namespace Rikkei\Notify\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Model\NotifyReciever;
use Rikkei\Core\Model\User;
use Rikkei\Notify\View\NotifyView;

class Job extends CoreModel
{

    public $appends = ['test'];

    protected $table = 'jobs';
    protected $guarded = [];

}
