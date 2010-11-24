<?
// Copyright (c) 2010 Guanoo, Inc.
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
    private static $connectors;
    private static $databases;
    private static $validators = array();
    private static $tables = array();
    private static $id_fields = array();
    private static $timestamp = array();
    private static $virtual = array();
    private $validation_messages;
    private $connector;
    private $database;
    private $table;
    private $data;
    private $to_update;
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

    public function set_connector($connector_class, $database = NULL)
    {
        if (!is_null($database)) $this->set_database($database);
        require_once 'lib/data/' . $connector_class . '.php';
        $connector_class = "Data_$connector_class";
        $database = $this->get_database();
        self::$connectors[$database] = new $connector_class();
        return $this;
    }

    public function get_connector()
    {
        if ($this->connector) return $this->connector;
        $database = $this->get_database();
        $this->connector = array_key(self::$connectors, $database, array_key(self::$connectors, 'models'));
        return $this->connector;
    }

    public function set_database($database)
    {
        $table = $this->get_table();
        $this->database = self::$databases[$table] = $database;
        return $this;
    }

    public function get_database()
    {
        if ($this->database) return $this->database;
        $table = $this->get_table();
        $this->database = array_key(self::$databases, $table, array_key(self::$databases, 'models'));
        return $this->database;
    }

    public function set_table($table)
    {
        $this->table = self::$tables[get_class($this)] = $table;
        return $this;
    }

    public function get_table()
    {
        if ($this->table) return $this->table;
        $this->table = array_key(self::$tables, get_class($this));
        if ($this->table) return $this->table;
        $this->set_table(Text::tableize(get_class($this)));
        return $this->table;
    }

    public function get_db_table()
    {
        $database = $this->get_database() . '.';        // MySQL databases
        if (strpos($database, '/') > 0) $database = ''; // SQLite file path
        return $database . $this->get_table();
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

    public function get_limit()
    {
        return $this->limit + 0;
    }

    public function set_offset($offset)
    {
        $this->offset = $offset + 0;
        return $this;
    }

    public function get_offset()
    {
        return $this->offset + 0;
    }

    public function set_id_field($field)
    {
        $table = $this->get_table();
        $this->id_field = self::$id_fields[$table] = $field;
        return $this;
    }

    public function get_id_field()
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

    public function set_id($id)
    {
        $id_field = $this->get_id_field();
        $this->$id_field = $id + 0;
        return $this;
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

    public function connect($database = NULL, $reconnect = FALSE)
    {
        if (is_null($database)) $database = $this->get_database();
        if ($reconnect) $this->connector = NULL;
        if (is_null($this->connector))
        {
            $this->connector = $this->get_connector()->connect($database);
        }
    }

    public function disconnect()
    {
        $this->get_connector()->disconnect();
        $this->connector = NULL;
    }

    public function quote($sql)
    {
        return '"' . $this->escape($sql) . '"';
    }

    public function quote_in($clause)
    {
        $parts = preg_split('/,\s*/', trim($clause, '()'));
        $quoted = array();
        foreach ($parts as $part) $quoted[] = $this->quote($part);
        return '(' . join(',', $quoted) . ')';
    }

    public function escape($sql)
    {
        $this->connect();
        return $this->get_connector()->escape($sql);
    }

    public function query($sql)
    {
        $this->connect();
        return $this->get_connector()->query($sql);
    }

    public function copy_to($other)
    {
        foreach ($this->data() as $field => $value)
        {
            $other->$field = $value;
        }
    }

    public function load($id = 0) // returns the object ID or zero on failure
    {
        if (is_null($id)) return 0;
        if ($id) $this->set_id($id);
        if ($found = $this->find_first())
        {
            $this->to_update = array();
            $this->data = $found->data;
            return $this->get_id();
        }
        return 0;
    }

    public function save() // returns true if the object saved or false if not
    {
        if (!$this->data || !$this->is_validated()) return FALSE;
        $saved = $this->get_id() ? $this->update() : $this->insert();
        return $saved ? TRUE : FALSE;
    }

    public function find_all($conditions = NULL) // returns array of objects
    {
        // Query the database

        $db_table = $this->get_db_table();
        $clause = $this->where_clause($conditions);
        $clause .= ($this->order ? ' order by ' . $this->order : '');
        $clause .= ($this->limit ? ' limit ' . $this->limit : '');
        $clause .= ($this->offset ? ' offset ' . $this->offset : '');
        $result = $this->query("select * from $db_table $clause");

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

    public function find_id($id) // returns an array of objects, or an object
    {
        $id_field = $this->get_id_field();
        return is_array($id) ?
            $this->find_all(array($id_field => 'in (' . join(',', $id) . ')')) :
            $this->find_first(array($id_field => $id + 0));
    }

    public function find_first($conditions = NULL) // returns an object or null
    {
        $old_limit = $this->get_limit();
        $objects = $this->set_limit(1)->find_all($conditions);
        $this->set_limit($old_limit);
        return count($objects) ? $objects[0] : NULL;
    }

    public function find_last($conditions = NULL) // returns an object or null
    {
        $objects = $this->find_all($conditions);
        return count($objects) ? $objects[count($objects) - 1] : NULL;
    }

    public function find_where($clause) // returns an array of objects
    {
        return $this->find_all(array('where' => $clause));
    }

    protected function where_clause($conditions = NULL) // returns SQL
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
        return $clause ? "where $clause" : '';
    }

    public function insert() // returns the ID of the inserted row, or zero
    {
        // Check there is no ID yet

        $id_field = $this->get_id_field();
        if (array_key($this->data, $id_field)) return 0; // already has ID!

        // Apply an optional "created_at" timestamp

        if ($this->has_timestamp('created_at'))
            $this->data['created_at'] = date('Y-m-d H:i:s');

        // Insert the new record into the table

        $db_table = $this->get_db_table();
        $fields = '';
        $values = '';
        foreach ($this->data as $field => $value)
        {
            if ($field == $id_field || $this->is_virtual($field)) continue;

            if ($fields) $fields .= ',';
            $fields .= $field;
            if ($values) $values .= ',';
            if ($field === 'password') $value = $this->password($value);
            $values .= $this->quote($value);
        }
        $this->query("insert into $db_table ($fields) values ($values)");

        // Return the new ID on the record

        $id_field = $this->get_id_field();
        $this->data[$id_field] = $this->connector->insert_id();
        return $this->data[$id_field];
    }

    public function update() // returns the object unless it has no ID field
    {
        // Check there's an ID value

        $id_field = $this->get_id_field();
        if (!array_key($this->data, $id_field)) return NULL; // no ID field!

        // Apply an optional "updated_at" timestamp

        if ($this->has_timestamp('updated_at'))
            $this->updated_at = date('Y-m-d H:i:s');

        // Did we provide a list of fields to update?

        $field_list = func_get_args();
        foreach ($field_list as $field) $this->to_update[$field] = TRUE;

        // Update the record values that have changed

        $db_table = $this->get_db_table();
        $updates = '';
        foreach ($this->data as $field => $value)
        {
            if ($field == $id_field || $this->is_virtual($field)) continue;
            if (!array_key_exists($field, $this->to_update)) continue;

            if ($field === 'password') $value = $this->password($value);
            $updates .= $field . '=' . $this->quote($value) . ',';
        }
        $updates = rtrim($updates, ','); // remove final comma
        if (!$updates) return $this;
        $updates .= " where $id_field=" . $this->data[$id_field];
        $this->query("update $db_table set $updates");
        $this->to_update = array();
        return $this;
    }

    public function update_all_fields()
    {
        foreach ($this->fields() as $field) $this->to_update[$field] = TRUE;
        return $this->update();
    }

    public function delete() // returns the object unless it has no ID field
    {
        // Check there's an ID value

        $id_field = $this->get_id_field();
        if (!array_key($this->data, $id_field)) return NULL; // no ID field!

        // Delete the record from the table

        $db_table = $this->get_db_table();
        $this->query("delete from $db_table where $id_field=" . $this->quote($this->data[$id_field]));
        $this->data[$id_field] = NULL;
        return $this;
    }

    public function delete_all()
    {
        $db_table = $this->get_db_table();
        $this->query("delete from $db_table");
        return $this;
    }

    public function drop()
    {
        $db_table = $this->get_db_table();
        $this->query("drop table $db_table");
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

    public function validation_messages()
    {
        return $this->validation_messages;
    }

    public function validation_message_for($field)
    {
        return array_key($this->validation_messages, $field);
    }

    public function is_validated()
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

    protected function is_valid_password($value, $password_repeat_field)
    {
        if ($value != $this->$password_repeat_field) return 'VALID_PASSWORDS_DO_NOT_MATCH';
    }
}

// End of Model.php
