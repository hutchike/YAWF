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

load_helper('Text'); // for "tableize"
load_interfaces('Modelled', 'Persisted', 'Validated');

/**
 * The SQL_model class links data objects to storage engines
 * that support SQL queries such as MySQL, SQLite2 & SQLite3.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class SQL_model extends Valid_model implements Modelled, Persisted, Validated
{
    private static $connectors;
    private static $databases;
    private static $tables = array();
    private static $id_fields = array();
    private static $timestamp = array();
    private static $virtual = array();
    private $connector;
    private $database;
    private $table;
    private $id_field;
    private $order;
    private $limit;
    private $offset;

    /**
     * Set the connector (e.g. "MySQLi") and optionally the database name
     *
     * @param String $connector_class the connector class name
     * @param Array $options an array of options (e.g. "database", "hostname")
     * @return Object this model object for method chaining
     */
    public function set_connector($connector_class, $options = array())
    {
        $database = array_key($options, Symbol::DATABASE);
        if (!is_null($database)) $this->set_database($database);
        require_once 'lib/connectors/' . $connector_class . '.php';
        $connector_class = "Data_$connector_class";
        $database = $this->get_database();
        self::$connectors[$database] = new $connector_class($options);
        return $this;
    }

    /**
     * Get the connector object
     *
     * @return Object the connector object for this model object
     */
    public function get_connector()
    {
        if ($this->connector) return $this->connector;
        $database = $this->get_database();
        $this->connector = array_key(self::$connectors, $database, array_key(self::$connectors, 'models'));
        if (!$this->connector) throw new Exception("No connector set for database \"$database\"");
        return $this->connector;
    }

    /**
     * Set the database used by this model object
     *
     * @param String $database the database name
     * @param String $connector the database connector class name (e.g. MySQLi)
     * @return Object this model object for method chaining
     */
    public function set_database($database, $connector = DB_CONNECTOR)
    {
        $table = $this->get_table();
        $this->database = self::$databases[$table] = $database;
        $this->set_connector($connector);
        return $this;
    }

    /**
     * Get the database used by this model object
     *
     * @return String the database used by this model object
     */
    public function get_database()
    {
        if ($this->database) return $this->database;
        $table = $this->get_table();
        $this->database = array_key(self::$databases, $table, array_key(self::$databases, 'models'));
        return $this->database;
    }

    /**
     * Set the table used by this model object
     *
     * @param String $table the table name
     * @return Object this model object for method chaining
     */
    public function set_table($table)
    {
        $this->table = self::$tables[$this->get_class()] = $table;
        return $this;
    }

    /**
     * Get the table used by this model object
     *
     * @return String the table used by this model object
     */
    public function get_table()
    {
        if ($this->table) return $this->table;
        $this->table = array_key(self::$tables, $this->get_class());
        if ($this->table) return $this->table;
        $this->set_table(Text::tableize($this->get_class()));
        return $this->table;
    }

    /**
     * Get the database and table used by this model object (e.g. "app.users")
     *
     * @return String the database and table used by this model object
     */
    public function get_db_table()
    {
        $database = $this->get_database() . '.';        // MySQL databases
        if (strpos($database, '/') > 0) $database = ''; // SQLite file path
        return $database . $this->get_table();
    }

    /**
     * Set the order for data result sets (i.e. "asc" or "desc")
     *
     * @param String $order the data result set order (i.e. "asc" or "desc")
     * @return Object this model object for method chaining
     */
    public function set_order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get the order for data result sets (i.e. "asc" or "desc")
     *
     * @return String the order for data result sets
     */
    public function get_order()
    {
        return $this->order;
    }

    /**
     * Set the limit for data result sets (e.g. 20)
     *
     * @param Integer $limit the data result set limit (e.g. 20)
     * @return Object this model object for method chaining
     */
    public function set_limit($limit)
    {
        $this->limit = $limit + 0;
        return $this;
    }

    /**
     * Get the limit for data result sets (e.g. 20)
     *
     * @return Integer the data result set limit (e.g. 20)
     */
    public function get_limit()
    {
        return $this->limit + 0;
    }

    /**
     * Set the offset for data result sets (e.g. 10)
     *
     * @param Integer $offset the data result set offset (e.g. 10)
     * @return Object this model object for method chaining
     */
    public function set_offset($offset)
    {
        $this->offset = $offset + 0;
        return $this;
    }

    /**
     * Get the offset for data result sets (e.g. 10)
     *
     * @return Integer the data result set offset (e.g. 10)
     */
    public function get_offset()
    {
        return $this->offset + 0;
    }

    /**
     * Set the ID field name for this model object
     *
     * @param String $field the ID field name for this model object
     * @return Object this model object for method chaining
     */
    public function set_id_field($field)
    {
        $table = $this->get_table();
        $this->id_field = self::$id_fields[$table] = $field;
        return $this;
    }

    /**
     * Get the ID field name for this model object
     *
     * @return String the ID field name for this model object
     */
    public function get_id_field()
    {
        if ($this->id_field) return $this->id_field;
        $table = $this->get_table();
        $this->id_field = array_key(self::$id_fields, $table, Symbol::ID);
        return $this->id_field;
    }

    /**
     * Get a data field value from this model object (with ID field mapping)
     *
     * @param String $field the data field to read
     * @return String the value of the data field
     */
    public function __get($field)
    {
        if ($field == Symbol::ID) $field = $this->get_id_field();
        return parent::__get($field);
    }

    /**
     * Set a data field value in this model object (with ID field mapping)
     *
     * @param String $field the data field to write
     * @param String $value the data value to write
     * @return String the value of the newly updated data field
     */
    public function __set($field, $value)
    {
        if ($field == Symbol::ID) $field = $this->get_id_field();
        return parent::__set($field, $value);
    }

    /**
     * Set some field flags (e.g. in the "virtual" or "timestamp" flag arrays)
     *
     * @param Array $array the array of field flags (e.g. "virtual")
     * @param Array $fields a list of fields to flag as TRUE
     * @return Object this model object for method chaining
     */
    private function set_field_flags(&$array, $fields)
    {
        $table = $this->get_table();
        foreach ($fields as $field)
        {
            $array["$table.$field"] = TRUE;
        }
        return $this;
    }

    /**
     * Set some fields as being timestamp fields on this model class
     *
     * @param Array an argument list of timestamp field names
     * @return Object this model object for method chaining
     */
    public function set_timestamp() // field list
    {
        $fields = func_get_args();
        return $this->set_field_flags(self::$timestamp, $fields);
    }

    /**
     * Get whether or not this model class has a particular timestamp field
     *
     * @param String the name of the timestamp field to look for
     * @return Boolean whether this model class has a particular timestamp field
     */
    public function has_timestamp($field)
    {
        $table = $this->get_table();
        return array_key(self::$timestamp, "$table.$field") ? TRUE : FALSE;
    }

    /**
     * Set some fields as being virtual fields to exclude from any SQL queries
     *
     * @param Array an argument list of virtual field names for this model class
     * @return Object this model object for method chaining
     */
    public function set_virtual() // field list
    {
        $fields = func_get_args();
        return $this->set_field_flags(self::$virtual, $fields);
    }

    /**
     * Get whether or not a particular field is virtual on this model class
     *
     * @param String the name of the virtual field to look for
     * @return Boolean whether the field is a virtual field on this model class
     */
    public function is_virtual($field)
    {
        $table = $this->get_table();
        return array_key(self::$virtual, "$table.$field") ? TRUE : FALSE;
    }

    /**
     * Connect this model to a database, optionally forcing a reconnection
     *
     * @param String $database the database name
     * @param Boolean $reconnect whether or not to reconnect (FALSE by default)
     */
    public function connect($database = NULL, $reconnect = FALSE)
    {
        if (is_null($database)) $database = $this->get_database();
        if ($reconnect) $this->connector = NULL;
        if (is_null($this->connector))
        {
            $this->connector = $this->get_connector()->connect($database);
        }
    }

    /**
     * Disonnect this model from a database
     */
    public function disconnect()
    {
        $this->get_connector()->disconnect();
        $this->connector = NULL;
    }

    /**
     * Quote some SQL to make it safe for executing in database queries
     *
     * @param String $sql the SQL to quote
     * @return String the quoted SQL, safe for executing in database queries
     */
    public function quote($sql)
    {
        return '"' . $this->escape($sql) . '"';
    }

    /**
     * Quote an SQL "in" clause to make it safe to execute in database queries
     *
     * @param String $clause the SQL "in" clause to quote
     * @return String the quoted SQL, safe for executing in database queries
     */
    public function quote_in($clause)
    {
        $parts = preg_split('/,\s*/', trim($clause, '()'));
        $quoted = array();
        foreach ($parts as $part) $quoted[] = $this->quote($part);
        return '(' . join(',', $quoted) . ')';
    }

    /**
     * Escape some SQL to make it safe to execute in database queries
     *
     * @param String $sql the SQL to escape by calling the connector object
     * @return String the quoted SQL, safe for executing in database queries
     */
    public function escape($sql)
    {
        $this->connect();
        return $this->get_connector()->escape($sql);
    }

    /**
     * Execute a database query and return the result set data fetcher object
     *
     * @param String $sql the SQL to execute in the database query
     * @return Object a result set data fetcher object
     */
    public function query($sql)
    {
        $this->connect();
        return $this->get_connector()->query($sql);
    }

    /**
     * Load a model object by ID or by the other fields that have been set
     *
     * @param Integer $id an optional ID value to load
     * @return Integer the ID of the loaded model object, or zero on failure
     */
    public function load($id = 0)
    {
        if (is_null($id)) return 0; // to catch NULL parameters
        if ($id) $this->id = $id;
        if ($found = $this->find_first())
        {
            $this->changed = array();
            $this->data = $found->data;
            return $this->id;
        }
        return 0;
    }

    /**
     * Save this model object by inserting or updating it (if it has an ID)
     *
     * @return Boolean whether or not this model object was saved
     */
    public function save()
    {
        if (!$this->data() || !$this->is_validated()) return FALSE;
        $saved = $this->id ? $this->update() : $this->insert();
        return $saved ? TRUE : FALSE;
    }

    /**
     * Find all model objects that match the conditions or the object's fields
     *
     * @param Array $conditions an optional array of conditions to match
     * @return Array a list of model objects that match the conditions or fields
     */
    public function find_all($conditions = array())
    {
        // Query the database

        $db_table = $this->get_db_table();
        $join = $this->join_clause($conditions);
        $where = $this->where_clause($conditions);
        $where .= ($this->order ? ' order by ' . $this->order : '');
        $where .= ($this->limit ? ' limit ' . $this->limit : '');
        $where .= ($this->offset ? ' offset ' . $this->offset : '');
        $result = $this->query("select * from $db_table $join $where");

        // ...to make objects

        $objects = array();
        $class = $this->get_class();
        while ($object = $result->fetch_object())
        {
            $objects[] = new $class($object, FALSE); // FALSE == has not changed
        }
        $result->close();
        return $objects;
    }

    /**
     * Find model objects with a particular ID, or array of IDs
     *
     * @param Integer $id the model ID to find (may also be an array of IDs)
     * @return SQL_model(s) the found model(s)
     */
    public function find_id($id)
    {
        $id_field = $this->get_id_field();
        return is_array($id) ?
            $this->find_all(array($id_field => 'in (' . join(',', $id) . ')')) :
            $this->find_first(array($id_field => $id + 0));
    }

    /**
     * Find the first model object that matches some conditions or field values
     *
     * @param Array $conditions an array of conditions to match (optional)
     * @return SQL_model the first matching model object, or NULL if none found
     */
    public function find_first($conditions = array())
    {
        $old_limit = $this->get_limit();
        $objects = $this->set_limit(1)->find_all($conditions);
        $this->set_limit($old_limit);
        return count($objects) ? $objects[0] : NULL;
    }

    /**
     * Find the last model object that matches some conditions or field values
     *
     * @param Array $conditions an array of conditions to match (optional)
     * @return SQL_model the last matching model object (may take some time!)
     */
    public function find_last($conditions = array())
    {
        $objects = $this->find_all($conditions);
        return count($objects) ? $objects[count($objects) - 1] : NULL;
    }

    /**
     * Find model objects that match a SQL "where" clause
     *
     * @param String $clause a SQL "where" clause to match
     * @param Array $conditions an array of conditions, e.g. a "join" clause
     * @return Array a list of model objects that match the SQL "where" clause
     */
    public function find_where($clause, $conditions = array())
    {
        $conditions['where'] = $clause;
        return $this->find_all($conditions);
    }

    /**
     * Return a SQL "join" clause for an array of conditions, or field values
     *
     * @param Array $conditions an array of conditions (optional)
     * @return String a SQL "where" clause from the conditions or field values
     */
    protected function join_clause($conditions = array())
    {
        if (!$conditions) return '';
        if ($join = array_key($conditions, 'join')) return "left join $join";
        return '';
    }

    /**
     * Return a SQL "where" clause for an array of conditions, or field values
     *
     * @param Array $conditions an array of conditions (optional)
     * @return String a SQL "where" clause from the conditions or field values
     */
    protected function where_clause($conditions = array())
    {
        $conditions = $conditions ? $conditions : $this->data;
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

    /**
     * Insert this model object's data into the database via the connector
     *
     * @return Integer the ID of the inserted row, or zero on failure
     */
    public function insert()
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
        $this->changed = array();
        return $this->data[$id_field];
    }

    /**
     * Update this model object's data in the database via the connector.
     * Note that this method will only update the fields that have changed.
     *
     * @return SQL_model this model object for chaining, or NULL on failure
     */
    public function update()
    {
        // Check there's an ID value

        $id_field = $this->get_id_field();
        if (!array_key($this->data, $id_field)) return NULL; // no ID field!

        // Apply an optional "updated_at" timestamp

        if ($this->has_timestamp('updated_at'))
            $this->updated_at = date('Y-m-d H:i:s');

        // Did we provide a list of fields to update?

        $field_list = func_get_args();
        foreach ($field_list as $field) $this->changed[$field] = TRUE;

        // Update the record values that have changed

        $db_table = $this->get_db_table();
        $updates = '';
        foreach ($this->data as $field => $value)
        {
            if ($field == $id_field || $this->is_virtual($field)) continue;
            if (!array_key_exists($field, $this->changed)) continue;

            if ($field === 'password') $value = $this->password($value);
            $updates .= $field . '=' . $this->quote($value) . ',';
        }
        $updates = rtrim($updates, ','); // remove final comma
        if (!$updates) return $this;
        $updates .= " where $id_field=" . $this->data[$id_field];
        $this->query("update $db_table set $updates");
        $this->changed = array();
        return $this;
    }

    /**
     * Update this model object's data in the database via the connector.
     * Note that this method will update *all* this model object's fields
     * that are included in this model object's data array - see fields().
     * You can override this by providing an arg list of fields to update.
     *
     * @param Array optional list of fields to update (updates all by default)
     * @return SQL_model this model object for chaining, or NULL on failure
     */
    public function update_all_fields()
    {
        $fields = func_get_args();
        $count = count($fields);
        if ($count == 0) $fields = $this->fields(); // the most usual case
        elseif ($count == 1 && is_array($fields[0])) $fields = $fields[0];
        foreach ($fields as $field) $this->changed[$field] = TRUE;
        return $this->update();
    }

    /**
     * Delete this model object's data from the database via the connector
     *
     * @return SQL_model this model object for chaining, or NULL on failure
     */
    public function delete()
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

    /**
     * Delete all the model object data in the database for this model class!!!
     *
     * @return SQL_model this model object for chaining
     */
    public function delete_all()
    {
        $db_table = $this->get_db_table();
        $this->query("delete from $db_table");
        return $this;
    }

    /**
     * Drop the database table for this model class!!!
     *
     * @return SQL_model this model object for chaining
     */
    public function drop()
    {
        $db_table = $this->get_db_table();
        $this->query("drop table $db_table");
        return $this;
    }

    /**
     * Return an encrypted password ready to store in the database
     *
     * @param String $text the unencrypted password text to be encrypted
     * @return String an encrypted password ready to store in the database
     */
    protected function password($text)
    {
        return sha1(md5($text));
    }
}

// End of SQL_model.php
