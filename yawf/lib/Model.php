<?
// Copyright (c) 2009 Guanoo, Inc.
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

require_once "lib/data/Connector.php";
load_helper('Text'); // for "tableize"

class Model extends YAWF
{
    private static $connector;
    private static $database;
    private static $validators = array();
    private static $tables = array();
    private static $id_fields = array();
    private static $timestamp = array();
    private static $virtual = array();
    private $validation_messages;
    private $to_update;
    private $data;
    private $table;
    private $id_field;
    private $order;
    private $limit;
    private $offset;

    public function setup()
    {
        // Subclass in your models like this:

        // $this->set_id_field('table_id_field');
        // $this->set_virtual('transient_field');
        // $this->set_timestamp('created_at', 'updated_at');
        // $this->validates('email', 'is_valid_email');
    }

    public static function set_database($database)
    {
        self::$database = $database;
    }

    public static function get_database()
    {
        return self::$database;
    }

    public function __construct($data = array())
    {
        $this->validation_messages = array();
        $this->to_update = array();
        $this->data = (array)$data;
    }

    public function __get($field)
    {
        return array_key($this->data, $field);
    }

    public function __set($field, $value)
    {
        $this->to_update[$field] = TRUE;
        $this->data[$field] = $value;
        return $value;
    }

    public function data()
    {
        return $this->data;
    }

    public function fields()
    {
        return array_keys($this->data);
    }

    protected function get_table()
    {
        if ($this->table) return $this->table;
        $this->table = array_key(self::$tables, get_class($this));
        if ($this->table) return $this->table;
        $this->set_table(Text::tableize(get_class($this)));
        return $this->table;
    }

    protected function set_table($table)
    {
        $this->table = self::$tables[get_class($this)] = $table;
        return $this;
    }

    public function set_order($order)
    {
        $this->order = $order;
        return $this;
    }

    public function set_limit($limit)
    {
        $this->limit = $limit + 0;
        return $this;
    }

    public function set_offset($offset)
    {
        $this->offset = $offset + 0;
        return $this;
    }

    public function set_id_field($field)
    {
        $table = $this->get_table();
        $this->id_field = self::$id_fields[$table] = $field;
        return $this;
    }

    protected function get_id_field()
    {
        if ($this->id_field) return $this->id_field;
        $table = $this->get_table();
        $this->id_field = array_key(self::$id_fields, $table, 'id');
        return $this->id_field;
    }

    public function get_id()
    {
        $id_field = $this->get_id_field();
        return $this->$id_field;
    }

    private function set_field_flags(&$array, $fields)
    {
        $table = $this->get_table();
        foreach ($fields as $field)
        {
            $array["$table.$field"] = TRUE;
        }
        return $this;
    }

    public function set_timestamp() // field list
    {
        $fields = func_get_args();
        return $this->set_field_flags(self::$timestamp, $fields);
    }

    public function has_timestamp($field)
    {
        $table = $this->get_table();
        return array_key(self::$timestamp, "$table.$field") ? TRUE : FALSE;
    }

    public function set_virtual() // field list
    {
        $fields = func_get_args();
        return $this->set_field_flags(self::$virtual, $fields);
    }

    public function is_virtual($field)
    {
        $table = $this->get_table();
        return array_key(self::$virtual, "$table.$field") ? TRUE : FALSE;
    }

    public static function connect($reconnect = FALSE)
    {
        if ($reconnect) self::$connector = NULL;
        if (is_null(self::$connector))
        {
            $connector_class = DB_CONNECTOR;
            require_once 'lib/data/' . $connector_class . '.php';
            self::$connector = new $connector_class();
            self::$connector->connect(self::$database);
        }
    }

    public static function disconnect()
    {
        self::$connector->disconnect();
        self::$connector = NULL;
    }

    public static function quote($sql)
    {
        return '"' . self::escape($sql) . '"';
    }

    public static function quote_in($clause)
    {
        $parts = preg_split('/,\s*/', trim($clause, '()'));
        $quoted = array();
        foreach ($parts as $part) $quoted[] = self::quote($part);
        return '(' . join(',', $quoted) . ')';
    }

    public static function escape($sql)
    {
        self::connect();
        return self::$connector->escape($sql);
    }

    public static function query($sql)
    {
        self::connect();
        return self::$connector->query($sql);
    }

    public function load()
    {
        if ($found = $this->find_first())
        {
            $this->to_update = array();
            $this->data = $found->data;
        }
        return $this->get_id();
    }

    public function save()
    {
        if (! $this->validate_on_save()) return FALSE;
        return $this->get_id() ? $this->update() : $this->insert();
    }

    public function find_all($conditions = NULL)
    {
        // Query the database

        $table = $this->get_table();
        $clause = $this->where_clause($conditions);
        $clause .= ($this->order ? ' order by ' . $this->order : '');
        $clause .= ($this->limit ? ' limit ' . $this->limit : '');
        $clause .= ($this->offset ? ' offset ' . $this->offset : '');
        $result = $this->query("select * from $table $clause");

        // ...to make objects

        $objects = array();
        $class = get_class($this);
        while ($object = $result->fetch_object())
        {
            $objects[] = new $class($object);
        }
        $result->close();
        return $objects;
    }

    public function find_id($id)
    {
        $id_field = $this->get_id_field();
        return is_array($id) ?
            $this->find_all(array($id_field => 'in (' . join(',', $id) . ')')) :
            $this->find_first(array($id_field => $id + 0));
    }

    public function find_first($conditions = NULL)
    {
        $objects = $this->find_all($conditions);
        return count($objects) ? $objects[0] : NULL;
    }

    public function find_last($conditions = NULL)
    {
        $objects = $this->find_all($conditions);
        return count($objects) ? $objects[count($objects) - 1] : NULL;
    }

    public function find_where($clause)
    {
        return $this->find_all(array('where' => $clause));
    }

    protected function where_clause($conditions = NULL)
    {
        $conditions = is_null($conditions) ? $this->data : (array)$conditions;
        if ($clause = array_key($conditions, 'where')) return "where $clause";
        foreach ($conditions as $field => $condition)
        {
            if ($this->is_virtual($field)) continue;
            $op = ($condition !== trim($condition, '%') ? ' like ' : '=');
            if (preg_match('/^([<>]=?)\s*(.*)$/', $condition, $matches))
            {
                $op = $matches[1];
                $condition = $matches[2];
            }
            elseif (preg_match('/^in \((.*)\)$/', $condition, $matches))
            {
                $op = 'in';
                $condition = $this->quote_in($matches[1]);
            }
            if ($clause) $clause .= ' and ';
            if ($field === 'password') $condition = $this->password($condition);
            if ($op != 'in') $condition = $this->quote($condition);
            $clause .= "$field $op $condition";
        }
        return $clause ? "where $clause" : NULL;
    }

    public function insert()
    {
        // Check there is no ID yet

        $id_field = $this->get_id_field();
        if (array_key($this->data, $id_field)) return;

        // Apply an optional "created_at" timestamp

        if ($this->has_timestamp('created_at'))
            $this->data['created_at'] = date('Y-m-d H:i:s');

        // Insert the new record into the table

        $table = $this->get_table();
        $fields = '';
        $values = '';
        foreach ($this->data as $field => $value)
        {
            if ($this->is_virtual($field)) continue;

            if ($fields) $fields .= ',';
            $fields .= $field;
            if ($values) $values .= ',';
            if ($field === 'password') $value = $this->password($value);
            $values .= $this->quote($value);
        }
        $this->query("insert into $table ($fields) values ($values)");

        // Return the new ID on the record

        $id_field = $this->get_id_field();
        $this->data[$id_field] = self::$connector->insert_id();
        return $this->data[$id_field];
    }

    public function update()
    {
        // Check there's an ID value

        $id_field = $this->get_id_field();
        if (!array_key($this->data, $id_field)) return;

        // Apply an optional "updated_at" timestamp

        if ($this->has_timestamp('updated_at'))
            $this->data['updated_at'] = date('Y-m-d H:i:s');

        // Did we provide a list of fields to update?

        $field_list = func_get_args();
        foreach ($field_list as $field) $this->to_update[$field] = TRUE;

        // Update the record values that have changed

        $table = $this->get_table();
        $updates = '';
        foreach ($this->data as $field => $value)
        {
            if ($this->is_virtual($field)) continue;
            if (!array_key_exists($field, $this->to_update)) continue;

            if ($field === 'password') $value = $this->password($value);
            if ($field !== $id_field) $updates .= $field . '=' . $this->quote($value) . ',';
        }
        $updates = rtrim($updates, ','); // remove final comma
        if (!$updates) return;
        $updates .= " where $id_field=" . $this->data[$id_field];
        $this->query("update $table set $updates");
        $this->to_update = array();
        return $this;
    }

    public function delete()
    {
        // Check there's an ID value

        $id_field = $this->get_id_field();
        if (!array_key($this->data, $id_field)) return;

        // Delete the record from the table

        $table = $this->get_table();
        $this->query("delete from $table where $id_field=" . $this->quote($this->data[$id_field]));
        $this->data[$id_field] = NULL;
        return $this;
    }

    public function delete_all()
    {
        $table = $this->get_table();
        $this->query("delete from $table");
        return $this;
    }

    public function drop()
    {
        $table = $this->get_table();
        $this->query("drop table $table");
        return $this;
    }

    protected function password($text)
    {
        return sha1(md5($text));
    }

    // ----------------------- Model validation methods ------------------------

    protected function validates($field, $rule, $args = NULL)
    {
        $table = $this->get_table();
        $key = "$table.$field";
        if (!array_key(self::$validators, $key)) self::$validators[$key] = array();
        self::$validators[$key][] = array($rule, $args);
    }

    public function validation_message_for($field)
    {
        return array_key($this->validation_messages, $field);
    }

    private function validate_on_save()
    {
        $messages = array();
        $table = $this->get_table();
        foreach ($this->data as $field => $value)
        {
            $rules = array_key(self::$validators, "$table.$field", array());
            foreach ($rules as $rule_and_args)
            {
                list($rule, $args) = $rule_and_args;
                $message = $args ? $this->$rule($value, $args) : $this->$rule($value);
                if ($message) $messages[$field] = $message;
            }
        }
        $this->validation_messages = $messages;
        return !$messages;
    }

    protected function is_valid_person_name($name)
    {
        if (preg_match('/^[\w\s,\.\'\-]+$/', $name)) return NULL;
        return 'VALID_NAME_CAN_ONLY_INCLUDE_WORDS';
    }

    protected function is_valid_email($email)
    {
        $at = strpos($email, '@');
        if ($at === FALSE) return 'VALID_EMAIL_NEEDS_AN_AT';
        if ($at === 0) return 'VALID_EMAIL_CANNOT_START_WITH_AT';
        if ($at === strlen($email)-1) return 'VALID_EMAIL_CANNOT_END_WITH_AT';
        $dot = strpos($email, '.');
        if ($dot === FALSE) return 'VALID_EMAIL_NEEDS_A_DOT';
        if ($dot === 0) return 'VALID_EMAIL_CANNOT_START_WITH_DOT';
        if ($dot === strlen($email)-1) return 'VALID_EMAIL_CANNOT_END_WITH_DOT';
    }

    protected function is_valid_length($value, $args)
    {
        $shortest = array_key($args, 'shortest');
        $longest = array_key($args, 'longest');
        if ($shortest && strlen($value) < $shortest) return 'VALID_LENGTH_TOO_SHORT';
        if ($longest && strlen($value) > $longest) return 'VALID_LENGTH_TOO_LONG';
    }
}

// End of Model.php
