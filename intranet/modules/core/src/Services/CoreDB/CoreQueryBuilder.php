<?php

namespace Rikkei\Core\Services\CoreDB;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Rikkei\Core\Events\DBEvent;

/**
 * Description of CoreBuilder
 *
 * @author lamnv
 */
class CoreQueryBuilder extends QueryBuilder
{
    /**
     * override function insert of Builder
     * @param array $values
     * @return bool
     */
    public function insert(array $values)
    {
        $inserted = parent::insert($values);
        event(new DBEvent('inserted', $this->from, $values));
        return $inserted;
    }

    /**
     * overrid function update of Builder
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        $bindings = array_values(array_merge($values, $this->getBindings()));
        $sql = $this->grammar->compileUpdate($this, $values);

        $updated = parent::update($values);
        event(new DBEvent('updated', $this->from, ['sql' => $this->convertSql($sql, $bindings)]));
        return $updated;
    }

    /**
     * overrid function delete of Builder
     * @param type $id
     * @return int
     */
    public function delete($id = null)
    {
        $sql = $this->grammar->compileDelete($this);
        $bindings = $this->getBindings();

        $deleted = parent::delete($id);
        event(new DBEvent('deleted', $this->from, ['sql' => $this->convertSql($sql, $bindings)]));
        return $deleted;
    }

    /**
     * convert sql with binding parameters
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    public function convertSql($sql, $bindings)
    {
        $sql .= ' ';
        $arySql = explode('?', $sql);
        if (!$arySql) {
            return $sql;
        }
        $result = '';
        foreach ($arySql as $key => $str) {
            if (!isset($bindings[$key])) {
                continue;
            }
            $result .= $str . (is_numeric($bindings[$key]) ? $bindings[$key] : '"' . $bindings[$key] . '"');
        }
        return trim($result);
    }
}
