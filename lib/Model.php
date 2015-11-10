<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 05/03/2015
 * Time: 0:04
 */

abstract class Model implements JsonSerializable {
    static $tables;
    static $keys;
    static $jsonMappings;
    static $jsonBindings;

    private $_oldValues;
    private $_canSave;

    function __construct($data) {
        foreach($this as $key=>$value) {
            if ($key[0] != '_') {
                $this->_oldValues[$key] = $value;
            }
        }
        $this->_oldValues = array_merge($this->_oldValues, $data);
        $this->_canSave = true;
        if (is_array($data)) {
            foreach($data as $key=>$val) {
                $this->$key = $val;
            }
        }
    }

    /**
     * @return static
     */
    static function create() {
        $class = get_called_class();
        $key = self::$keys[$class];

        return new $class(array($key => null));
    }

    function disableSave() {
        $this->_canSave = false;
    }

    function __set($name, $value)
    {
        if (!isset($this->_oldValues[$name])) {
            $this->_oldValues[$name] = null;
        }
        $this->$name = $value;
    }

    function getSaveSQL() {

        $class = get_called_class();
        $table = self::$tables[$class];
        $primaryKey = self::$keys[$class];

        $id = $this->$primaryKey;

        if ($id === null) {
            $columns = array();
            $values = array();
            $params = array();

            foreach ($this->_oldValues as $key => $value) {
                if ($this->$key !== $this->_oldValues[$key]) {
                    $columns[] = "`$key`";
                    $values[] = '?';
                    $params[] = $this->$key;
                }
            }

            $sql = "insert into `{$table}` (";
            $sql .= implode(',', $columns);
            $sql .= ") values (";
            $sql .= implode(',', $values);
            $sql .= ")";

            return array(1, $columns, $params, null);
        }
        else {
            // update
            $changed = false;

            $changes = array();
            $params = array();

            foreach ($this->_oldValues as $key => $value) {
                if ($this->$key !== $value) {
                    $changes[] = "`$key` = ?";
                    $params[] = $this->$key;
                    $changed = true;
                }
            }

            if ($changed) {

                return array(2, $changes, $params, $id);
            }
            else return array(0, array(), array(), null);
        }
    }




    function save() {

        if (!$this->_canSave) return;

        $class = get_called_class();
        $table = self::$tables[$class];
        $primaryKey = self::$keys[$class];

        list($saveType, $columns, $params, $id) = $this->getSaveSQL();

        if ($saveType == 1) {
            // insert

            $values = array();

            foreach($params as $v) {
                $values[] = '?';
            }

            $sql = "insert into `{$table}` (";
            $sql .= implode(',', $columns);
            $sql .= ") values (";
            $sql .= implode(',', $values);
            $sql .= ")";

            R::exec($sql, $params);
            $this->$primaryKey = R::getDatabaseAdapter()->getInsertID();

        }
        elseif ($saveType == 2) {

            $sql = "update `{$table}` set ";
            $sql .= implode(', ', $columns);
            $sql .= " where `$primaryKey` = ?";
            $params[] = $id;

            R::exec($sql, $params);
            # $this->$primaryKey = R::getDatabaseAdapter()->getInsertID();
        }
    }


    static function getMultiple(array $ids) {
        if (empty($ids)) return array();

        $class = get_called_class();
        $key = self::$keys[$class];

        $values = array();
        $params = array();
        foreach($ids as $id) {
            $values[] = '?';
            $params[] = $id;
        }

        $values = implode(',', $values);
        return self::find("`$key` in ($values)", $params);
    }

    /**
     * @param $id
     * @return static
     */
    static function get($id) {

        $class = get_called_class();
        $table = self::$tables[$class];
        $key = self::$keys[$class];

        $row = R::getRow("select * from `$table` where `$key` = ? limit 1", array($id));

        if (!$row) {
            return null;
        }
        else {
            return new $class($row);
        }
    }

    /**
     * @param $conditions
     * @param array $binds
     * @return static[]
     */
    static function find($conditions, $binds=array()) {
        $result = array();

        $class = get_called_class();
        $table = self::$tables[$class];

        if (strlen(trim($conditions)) == 0) $conditions = '1 = 1';

        $rows = R::getAll("select * from `$table` where $conditions", $binds);

        foreach($rows as $row) {
            $result[] = new $class($row);
        }

        return $result;
    }

    /**
     * @param $conditions
     * @param array $binds
     * @return static
     */
    static function findOne($conditions, $binds=array()) {
        $result = self::find($conditions, $binds);
        if (count($result)) {
            return $result[0];
        }
        else {
            return null;
        }
    }

    static function init($table, $key='id', $JSONmappings = array(), $JSONbindings = array()) {
        $class = get_called_class();
        self::$tables[$class] = $table;
        self::$keys[$class] = $key;
        self::$jsonMappings[$class] = $JSONmappings;
        self::$jsonBindings[$class] = $JSONbindings;
    }

    /**
     * @param $items
     * @param $field
     * @param null $indexField
     * @return array
     */
    static function pluck($items, $field, $indexField=null) {
        $result = array();

        if ($indexField === null) {
            foreach($items as $item) {
                if (!is_array($item)) {
                    $result[] = $item->$field;
                }
                else {
                    $result[] = $item[$field];
                }
            }
        } else {
            foreach($items as $item) {
                if (property_exists($item, $indexField)) {
                    $result[$item->$indexField] = $item->$field;
                }
            }
        }
        return $result;
    }

    /**
     * @param $items
     * @param $field
     * @return array
     */
    static function groupBy($items, $field) {
        $result = array();

        foreach($items as $item) {
            $value = $item->$field;
            if (!isset($result[$value])) {
                $result[$value] = array();
            }
            $result[$value][] = $item;
        }
        return $result;
    }

    static function orderBy($items, $field) {
        $keys = Model::pluck($items, $field);
        array_multisort($keys, $items);
        return $items;
    }

    /**
     * @param $items
     * @param $field
     * @return array
     */
    static function indexBy($items, $field) {
        $result = array();

        foreach($items as $item) {
            $value = $item->$field;
            $result[$value] = $item;
        }
        return $result;
    }

    /**
     * @param $arr Model[]
     */
    static function saveAll($arr) {

        if (!count($arr)) return;

        $columns = array();
        $rows = array();

        $class = get_class($arr[0]);
        $table = self::$tables[$class];
        $primaryKey = self::$keys[$class];

        $updates = array();
        $updateIds = array();

        foreach($arr as $obj) {

            if (get_class($obj) != $class) {
                throw new Exception('saveAll() solo funciona para objetos de la misma clase');
            }

            list($type, $sql, $params, $id) = $obj->getSaveSQL();
            if ($type == 2) {
                $serialized = serialize(array($sql, $params));
                if (!isset($updates[$serialized])) {
                    $updates[$serialized] = array($sql, $params);
                    $updateIds[$serialized] = array();
                }
                $updateIds[$serialized][] = $id;
            }
            else {
                $row = array();
                foreach($sql as $index => $column) {
                    $columns[$column] = true;
                    $row[$column] = $params[$index];
                }
                $rows[] = $row;
            }
        }

        foreach($updates as $key => $arr) {
            list($changes, $params) = $arr;
            $sql = "update `{$table}` set ";
            $sql .= implode(', ', $changes);
            $sql .= " where `$primaryKey` IN (";

            foreach($updateIds[$key] as $updateId) {
                $params[] = $updateId;
                $sql .= "?,";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ")";
            R::exec($sql, $params);
        }

        if (count($rows)) {

            $params = array();
            $columns = array_keys($columns);

            $values = array();

            foreach($columns as $column) {
                $values[] = '?';
            }
            $values = implode(',', $values);

            $sql = "insert into `{$table}` (";
            $sql .= implode(',', $columns);
            $sql .= ") values ";

            foreach($rows as $row) {
                $sql .= "($values), ";
                foreach($columns as $column) {
                    if (isset($row[$column])) {
                        $params[] = $row[$column];
                    }
                    else {
                        $params[] = null;
                    }
                }
            }
            $sql = substr($sql, 0, -2);

            R::exec($sql, $params);
        }
    }

    function delete() {
        $class = get_called_class();
        $table = self::$tables[$class];
        $primaryKey = self::$keys[$class];

        $id = $this->$primaryKey;

        if ($id !== null) {
            R::exec("delete from `$table` where `$primaryKey` = ? limit 1", array($id));
            foreach($this->_oldValues as $key => $value) {
                $this->_oldValues[$key] = null;
            }
            $this->$primaryKey = null;
            return true;
        }

        return false;
    }

    function JSONSerialize() {
        $class = get_called_class();
        $mappings = isset(self::$jsonMappings[$class])?self::$jsonMappings[$class]:array();
        $bindings = isset(self::$jsonBindings[$class])?self::$jsonBindings[$class]:array();
        $result = array();

        foreach($mappings as $key => $property) {
            $result[$key] = $this->$property;
        }

        foreach($bindings as $key => $method) {
            $result[$key] = $this->$method();
        }

        ksort($result);
        return $result;
    }
}
Model::$keys = array();
Model::$tables = array();
Model::$jsonMappings = array();
Model::$jsonBindings = array();