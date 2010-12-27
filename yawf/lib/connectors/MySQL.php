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

load_interface('SQL_connector');

/**
 * Connect to a MySQL database via the older MySQL PHP extension.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Data_MySQL extends YAWF implements SQL_connector
{
    private $hostname;
    private $username;
    private $password;
    private $dbh;

    /**
     * Create a new Data_MySQL object
     *
     * @param Array $options an array of options (hostname, username, password)
     */
    public function __construct($options = array())
    {
        $this->hostname = array_key($options, Symbol::HOSTNAME, DB_HOSTNAME);
        $this->username = array_key($options, Symbol::USERNAME, DB_USERNAME);
        $this->password = array_key($options, Symbol::PASSWORD, DB_PASSWORD);
    }

    /**
     * Connect to a database
     *
     * @param String $db the database name (optional)
     */
    public function connect($database = DB_DATABASE)
    {
        $this->dbh = mysql_connect($this->hostname, $this->username, $this->password);
        if (!$this->dbh) throw new Exception('Cannot connect: ' . var_export($this, TRUE));
        mysql_select_db($database, $this->dbh);
    }

    /**
     * Disconnect from a database
     */
    public function disconnect()
    {
        mysql_close($this->dbh);
    }

    /**
     * Escape a string of SQL so that it's safe to execute
     *
     * @param String $sql the string of SQL to escape
     * @return String safe SQL ready for executing in a query
     */
    public function escape($sql)
    {
        return mysql_real_escape_string($sql);
    }

    /**
     * Execute an SQL query and return a result object fetcher
     *
     * @param String $sql the SQL to execute in the query
     * @return Object a fetcher object with "fetch_object" and "close" methods
     */
    public function query($sql)
    {
        $result = mysql_query($sql, $this->dbh);
        $error = $this->error();
        if ($error) throw new Exception("Database error: $error");
        return new MySQL_fetcher($result);
    }

    /**
     * Return the last insert ID after an insert query has been executed
     *
     * @return Integer the last insert ID number
     */
    public function insert_id()
    {
        return mysql_insert_id($this->dbh);
    }

    /**
     * Return any error text about the most recent query
     *
     * @return String any error text about the most recent query
     */
    public function error()
    {
        return mysql_error($this->dbh);
    }
}

/**
 * Fetch result set rows from a MySQL database as objects
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class MySQL_fetcher extends YAWF
{
    private $result;

    /**
     * Create a new MySQL_fetcher object
     *
     * @param Object $result a result set
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * Close the result set
     */
    public function close()
    {
        mysql_free_result($this->result);
        $this->result = NULL;
    }

    /**
     * Fetch the next object from the result set
     *
     * @return Object the next object from the result set, or NULL when done
     */
    public function fetch_object()
    {
        return mysql_fetch_object($this->result);
    }
}

// End of MySQL.php
