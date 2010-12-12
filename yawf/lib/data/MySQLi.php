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

/**
 * Connect to a MySQL database via the MySQLi PHP extension.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Data_MySQLi extends YAWF implements Connector
{
    private $hostname;
    private $username;
    private $password;
    private $mysqli;

    /**
     * Create a new Data_MySQLi object
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
        $this->mysqli = new mysqli($this->hostname, $this->username, $this->password, $database);
        if (mysqli_connect_errno()) throw new Exception('Cannot connect: ' . var_export($this, TRUE));
    }

    /**
     * Disconnect from a database
     */
    public function disconnect()
    {
        $this->mysqli->close();
    }

    /**
     * Escape a string of SQL so that it's safe to execute
     *
     * @param String $sql the string of SQL to escape
     * @return String safe SQL ready for executing in a query
     */
    public function escape($sql)
    {
        return $this->mysqli->real_escape_string($sql);
    }

    /**
     * Execute an SQL query and return a result object fetcher
     *
     * @param String $sql the SQL to execute in the query
     * @return Object a fetcher object with "fetch_object" and "close" methods
     */
    public function query($sql)
    {
        $result = $this->mysqli->query($sql);
        $error = $this->error();
        if ($error) throw new Exception("Database error: $error");
        return $result;
    }

    /**
     * Return the last insert ID after an insert query has been executed
     *
     * @return Integer the last insert ID number
     */
    public function insert_id()
    {
        return $this->mysqli->insert_id;
    }

    /**
     * Return any error text about the most recent query
     *
     * @return String any error text about the most recent query
     */
    public function error()
    {
        return $this->mysqli->error;
    }
}

// End of MySQLi.php
