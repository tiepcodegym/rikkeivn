<?php

namespace Rikkei\Core\Services\CoreDB;

use Illuminate\Database\DatabaseManager;
use Rikkei\Core\Services\CoreDB\CoreConnectionFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connectors\ConnectionFactory;

/**
 * Description of CoreDB
 *
 * @author lamnv
 */
class CoreDatabaseManager extends DatabaseManager
{
    /**
     * @param type $app
     */
    public function __construct($app, ConnectionFactory $factory)
    {
        $this->app = $app;
        $this->factory = $this->initConnectionFactory($app);
    }

    /**
     * init CoreConnectionFactory
     * @param Container $container
     * @return CoreConnectionFactory
     */
    public function initConnectionFactory(Container $container)
    {
        return new CoreConnectionFactory($container);
    }

}
