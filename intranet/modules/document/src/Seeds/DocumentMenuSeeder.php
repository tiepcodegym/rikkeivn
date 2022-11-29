<?php

namespace Rikkei\Document\Seeds;

use Rikkei\Core\Seeds\MenuItemsSeeder;

class DocumentMenuSeeder extends MenuItemsSeeder
{

    protected $initOrder = 14;
    protected $checkVersion = 'DocumentMenuSeeder-v2';
    
    public function __construct() {
        $this->configFile = RIKKEI_DOC_PATH . 'config/menu.php';
    }
}


