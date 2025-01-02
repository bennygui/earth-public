<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace BX\DB;

require_once('Meta.php');
require_once('UI.php');

// Annotations used by this module:
// @dbcol: Public property must have this to be read/written to the database.
// @dbkey: This @dbcol is a key of the table. There can be multiple @dbkey.
// @dbclassid: This column contains the name of a subclass of the table row. There can be only one column with @dbclassid.
// @dbdefault: This column has a default in the database, so don't try to insert a NULL value.
// @dbautoincrement: This column is autoincrement so read the value from the database when inserting
// @dboptional: This column could not be in the table, must check if it exists before accessing.

const MAX_JSON_SIZE = 65535;

class RowMgrRegister
{
    private static $classId;
    private static $rowMgrs = [];

    public static function registerClassId(string $classId)
    {
        if (self::$classId !== null && self::$classId != $classId) {
            throw new \BgaSystemException("BUG! Database row manager is already registered");
        }
        self::$classId = $classId;
    }

    public static function newMgr(...$args)
    {
        $classId = self::$classId;
        if ($classId === null) {
            $classId = RowMgr::class;
        }
        $mgr = \BX\Meta\newWithConstructor($classId, $args);
        self::$rowMgrs[] = $mgr;
        return $mgr;
    }

    public static function clearAllMgrCache()
    {
        foreach (self::$rowMgrs as $mgr) {
            $mgr->clearCache();
        }
    }
}

class ColumnProperty
{
    private $property;
    private $column;

    private function __construct()
    {
    }

    public static function fromProperty(string $property)
    {
        $v = new self();
        $v->property = $property;
        $v->column = self::propertyToColumnName($property);
        return $v;
    }

    public static function fromColumn(string $column)
    {
        $v = new self();
        $v->column = $column;
        $v->property = self::columnNameToProperty($column);
        return $v;
    }

    public function property()
    {
        return $this->property;
    }

    public function column()
    {
        return $this->column;
    }

    public static function propertyToColumnName(string $name)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }

    public static function columnNameToProperty(string $name)
    {
        return lcfirst(str_replace('_', '', ucwords($name, '_')));
    }
}

abstract class BaseRow extends \BX\UI\UISerializable
{
    public function keyValue()
    {
        $meta = new \BX\Meta\Annotation(get_class($this));
        $properties = $meta->getPropertiesWithAnnotation("@dbkey");
        if (count($properties) != 1) {
            throw new \BgaSystemException("BUG! keyValue requires exactly 1 key column");
        }
        $property = array_shift($properties);
        return $this->$property;
    }

    public function setKeyValue($keyValue)
    {
        $meta = new \BX\Meta\Annotation(get_class($this));
        $properties = $meta->getPropertiesWithAnnotation("@dbkey");
        if (count($properties) != 1) {
            throw new \BgaSystemException("BUG! setKeyValue requires exactly 1 key column");
        }
        $property = array_shift($properties);
        $this->$property = $keyValue;
    }
}

class RowMgr
{
    protected $db;
    protected $tableName;
    protected $baseRowClassName;
    private $tableIsOptional;
    private $selectRowsCache;
    private $useCache;
    private $missingOptionalColumns;
    private $onChangingCallbacks;

    public function __construct(string $tableName, string $baseRowClassName)
    {
        $this->db = new class extends \APP_DbObject
        {
            public function executeQuery(string $sql)
            {
                return $this->DBQuery($sql);
            }
            public function executeSelect(string $sql)
            {
                return $this->getObjectListFromDB($sql);
            }
            public function executeGetLastId()
            {
                return $this->DbGetLastId();
            }
        };
        $this->tableName = $tableName;
        $this->baseRowClassName = $baseRowClassName;
        $this->tableIsOptional = false;
        $this->selectRowsCache = [];
        $this->useCache = true;
        $this->missingOptionalColumns = null;
        $this->onChangingCallbacks = [fn () => $this->clearCache()];
    }

    public function setUseCache(bool $useCache = true)
    {
        $this->useCache = $useCache;
        $this->clearCache();
    }

    public function setTableIsOptional(bool $isOptional = true)
    {
        $this->tableIsOptional = $isOptional;
        $this->clearCache();
    }

    public function registerOnChanging(callable $callback)
    {
        $this->onChangingCallbacks[] = $callback;
    }

    private function onChanging()
    {
        foreach ($this->onChangingCallbacks as $f) {
            $f();
        }
    }

    public function newRow(?string $rowClassName = null)
    {
        if ($rowClassName === null) {
            return new $this->baseRowClassName;
        } else {
            return new $rowClassName;
        }
    }

    public function insertRow(BaseRow $row)
    {
        $this->onChanging();
        if ($this->skipOptionalTable()) {
            return;
        }
        $columns = $this->getColumns();
        $colClassId = $this->getColumnClassId();
        if ($colClassId !== null) {
            $property = $colClassId->property();
            $row->$property = get_class($row);
        }

        $columnsWithDefault =  array_flip(array_map(function ($c) {
            return $c->column();
        }, $this->getColumnsWithDefault()));
        $dbColumns = [];
        $dbValues = [];
        foreach ($columns as $c) {
            $column = $c->column();
            $property = $c->property();
            if (array_key_exists($column, $columnsWithDefault) && $row->$property === null) {
                continue;
            }
            $dbColumns[] = $column;
            $dbValues[] = self::sqlNullOrValue($row->$property);
        }

        $dbColumnsStr = implode(',', $dbColumns);
        $dbValuesStr = implode(',', $dbValues);

        $sql = "INSERT INTO {$this->tableName} ($dbColumnsStr) VALUES ($dbValuesStr)";
        $this->executeQuery($sql);
        $autoIncColumn = $this->getColumnAutoIncrement();
        if ($autoIncColumn !== null) {
            $property = $autoIncColumn->property();
            $row->$property = $this->executeGetLastId();
        }
    }

    public function updateRow(BaseRow $row)
    {
        $this->onBeforeUpdateRow($row);
        $this->onChanging();
        if ($this->skipOptionalTable()) {
            return;
        }
        $dbValues = implode(', ', array_map(function ($c) use ($row) {
            $p = $c->property();
            return $c->column() . " = " . self::sqlNullOrValue($row->$p);
        }, $this->getColumns()));

        $dbKeys = implode(' AND ', array_map(function ($c) use ($row) {
            $p = $c->property();
            return $c->column() . " = " . self::sqlNullOrValue($row->$p);
        }, $this->getColumnKeys()));

        $sql = "UPDATE {$this->tableName} SET $dbValues WHERE $dbKeys";
        $this->executeQuery($sql);
    }

    public function deleteRow(BaseRow $row)
    {
        $this->onChanging();
        if ($this->skipOptionalTable()) {
            return;
        }
        $dbKeys = implode(' AND ', array_map(function ($c) use ($row) {
            $p = $c->property();
            return $c->column() . " = " . self::sqlNullOrValue($row->$p);
        }, $this->getColumnKeys()));

        $sql = "DELETE FROM {$this->tableName} WHERE $dbKeys";
        $this->executeQuery($sql);
    }

    public function deleteRowsWhereEqual(string $column, $value)
    {
        $this->onChanging();
        if ($this->skipOptionalTable()) {
            return;
        }

        $where = $column . " = " . self::sqlNullOrValue($value);

        $sql = "DELETE FROM {$this->tableName} WHERE $where";
        $this->executeQuery($sql);
    }

    public function deleteAllRows()
    {
        $this->onChanging();
        if ($this->skipOptionalTable()) {
            return;
        }
        $sql = "DELETE FROM {$this->tableName}";
        $this->executeQuery($sql);
    }

    public function getAllRows(string $order = null)
    {
        if ($this->skipOptionalTable()) {
            return [];
        }
        $allRows = [];
        $columns = $this->getColumns();

        $dbColumns = implode(',', array_map(function ($c) {
            return $c->column();
        }, $columns));

        $orderBy = '';
        if ($order !== null) {
            $orderBy = " ORDER BY $order";
        }

        $sql = "SELECT $dbColumns FROM {$this->tableName} $orderBy";
        foreach ($this->executeSelect($sql) as $row) {
            $allRows[] = $this->rowToClass($row);
        }
        return $allRows;
    }

    public function getAllRowsByKey()
    {
        if ($this->skipOptionalTable()) {
            return [];
        }
        $columnKeys = $this->getColumnKeys();
        if (count($columnKeys) != 1) {
            throw new \BgaSystemException("BUG! getAllRowsByKey requires exactly 1 key column");
        }
        $property = $columnKeys[0]->property();
        $rows = $this->getAllRows();
        return array_combine(array_map(function ($r) use ($property) {
            return $r->$property;
        }, $rows), $rows);
    }

    public function getRowByKey($key)
    {
        if ($this->skipOptionalTable()) {
            return null;
        }
        $columnKeys = $this->getColumnKeys();
        if (count($columnKeys) != 1) {
            throw new \BgaSystemException("BUG! getRowByKey requires exactly 1 key column");
        }
        $dbKey = $columnKeys[0]->column() . " = " . self::sqlNullOrValue($key);

        $columns = $this->getColumns();
        $dbColumns = implode(',', array_map(function ($c) {
            return $c->column();
        }, $columns));

        $sql = "SELECT $dbColumns FROM {$this->tableName} WHERE $dbKey";
        foreach ($this->executeSelect($sql) as $row) {
            return $this->rowToClass($row);
        }
        return null;
    }

    protected function onBeforeUpdateRow(BaseRow $row)
    {
    }

    public function clearCache()
    {
        $this->selectRowsCache = [];
    }

    protected function skipOptionalTable()
    {
        if ($this->tableIsOptional) {
            return (!$this->tableExists());
        }
        return false;
    }

    protected function tableExists()
    {
        $sql = "SHOW TABLES LIKE '{$this->tableName}'";
        foreach ($this->executeSelect($sql) as $row) {
            return true;
        }
        return false;
    }

    protected function getColumns()
    {
        $columns = [];
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        $missingProperties = array_flip(
            array_map(
                fn ($c) => $c->property(),
                $this->getMissingOptionalColumns()
            )
        );
        foreach ($meta->getPropertiesWithAnnotation("@dbcol") as $property) {
            if (array_key_exists($property, $missingProperties)) {
                continue;
            }
            $columns[] = ColumnProperty::fromProperty($property);
        }
        return $columns;
    }

    protected function getColumnKeys()
    {
        $columns = [];
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        foreach ($meta->getPropertiesWithAnnotation("@dbkey") as $property) {
            $columns[] = ColumnProperty::fromProperty($property);
        }
        return $columns;
    }

    protected function getColumnAutoIncrement()
    {
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        foreach ($meta->getPropertiesWithAnnotation("@dbautoincrement") as $property) {
            return ColumnProperty::fromProperty($property);
        }
        return null;
    }

    protected function getColumnsWithDefault()
    {
        $columns = [];
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        foreach ($meta->getPropertiesWithAnnotation("@dbdefault") as $property) {
            $columns[] = ColumnProperty::fromProperty($property);
        }
        return $columns;
    }

    protected function getColumnClassId()
    {
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        foreach ($meta->getPropertiesWithAnnotation("@dbclassid") as $property) {
            return ColumnProperty::fromProperty($property);
        }
        return null;
    }

    protected function getColumnsWithOptional()
    {
        $columns = [];
        $meta = new \BX\Meta\Annotation($this->baseRowClassName);
        foreach ($meta->getPropertiesWithAnnotation("@dboptional") as $property) {
            $columns[] = ColumnProperty::fromProperty($property);
        }
        return $columns;
    }

    protected function getMissingOptionalColumns()
    {
        if ($this->missingOptionalColumns !== null) {
            return $this->missingOptionalColumns;
        }
        $optional = $this->getColumnsWithOptional();
        if (count($optional) == 0) {
            $this->missingOptionalColumns = [];
        } else {
            $seenColumns = [];
            $sql = "SHOW COLUMNS FROM {$this->tableName}";
            foreach ($this->executeSelect($sql) as $row) {
                $seenColumns[$row['Field']] = true;
            }
            $this->missingOptionalColumns = array_values(
                array_filter(
                    $optional,
                    fn ($c) => !array_key_exists($c->column(), $seenColumns)
                )
            );
        }
        return $this->missingOptionalColumns;
    }

    public function executeQuery(string $sql)
    {
        if ($this->skipOptionalTable()) {
            return;
        }
        $this->db->executeQuery($sql);
    }

    public function executeSelect(string $sql)
    {
        if (!array_key_exists($sql, $this->selectRowsCache)) {
            $this->selectRowsCache[$sql] = $this->db->executeSelect($sql);
        }
        $ret = $this->selectRowsCache[$sql];
        if (!$this->useCache) {
            $this->clearCache();
        }
        return $ret;
    }

    public function executeGetLastId()
    {
        if ($this->skipOptionalTable()) {
            return 0;
        }
        return $this->db->executeGetLastId();
    }

    private function rowToClass($row)
    {
        $classId = $this->baseRowClassName;
        $colClassId = $this->getColumnClassId();
        if ($colClassId !== null) {
            $classId = $row[$colClassId->column()];
        }
        $rowClass = new $classId;
        foreach ($row as $column => $value) {
            $property = ColumnProperty::columnNameToProperty($column);
            $rowClass->$property = $value;
        }
        return $rowClass;
    }

    public static function sqlNullOrValue($value)
    {
        if ($value === null) {
            return "NULL";
        }
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        } else if (is_bool($value)) {
            return ($value ? "1" : "0");
        } else {
            return "$value";
        }
    }
}

function convertFromValueToJsonForColumn($value)
{
    $json = json_encode(\BX\Meta\extractAllPropertyValues($value));
    if (strlen($json) > MAX_JSON_SIZE) {
        throw new \BgaSystemException('BUG! convertToJsonForColumnis too long: ' . strlen($json));
    }
    return $json;
}

function convertFromJsonToValueForColumn($json)
{
    return \BX\Meta\rebuildAllPropertyValues(json_decode($json, true));
}
