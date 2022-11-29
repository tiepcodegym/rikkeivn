<?php

namespace Rikkei\Api\Helper;

use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Team\Model\Team as TeamModel;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class HrmBase extends BaseHelper
{
    protected static $instance;

    public function __construct()
    {
        $this->model = TeamModel::class;
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
