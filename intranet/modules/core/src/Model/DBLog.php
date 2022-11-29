<?php

namespace Rikkei\Core\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\CacheHelper;

class DBLog extends CoreModel
{
    protected $table = 'db_logs';
    protected $fillable = ['action', 'model', 'subject_id', 'attributes', 'actor_id'];

    const KEY_TABLES = 'db_log.tables';

    public static function getGridData()
    {
        $pager = Config::getPagerData();

        $collection = self::select('db.action', 'db.model', 'db.subject_id', 'db.attributes', 'emp.email', 'db.created_at')
                ->from(self::getTableName() . ' as db')
                ->leftJoin(Employee::getTableName() . ' as emp', 'db.actor_id', '=', 'emp.id')
                ->groupBy('db.id');

        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get config list tables save log
     * @return array
     */
    public static function getSaveLogTables()
    {
        $tableNames = '';
        if ($strTables = CacheHelper::get(self::KEY_TABLES)) {
            $tableNames = $strTables;
        } else {
            $tableNames = CoreConfigData::getValueDb(self::KEY_TABLES);
        }
        $aryTables = explode(',', trim($tableNames));
        if (!$aryTables) {
            return [];
        }
        $aryTables = array_map(function ($table) {
            return trim($table);
        }, $aryTables);
        return $aryTables;
    }
}
