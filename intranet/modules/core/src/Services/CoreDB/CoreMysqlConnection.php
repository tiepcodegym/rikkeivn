<?php

namespace Rikkei\Core\Services\CoreDB;

use Illuminate\Database\MySqlConnection;
use Rikkei\Core\Services\CoreDB\CoreQueryBuilder;

/**
 * Description of CoreMysqlConnection
 *
 * @author lamnv
 */
class CoreMysqlConnection extends MySqlConnection
{
    /**
     * override function query of MysqlConnection
     * @return CoreQueryBuilder
     */
    public function query() {
        return new CoreQueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }
}
