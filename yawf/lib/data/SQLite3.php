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
 * Connect to a SQLite3 database via the PDO SQLite PHP extension.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Data_SQLite3 extends YAWF implements Connector
{
    private $sqlite_db;

    /**
     * Create a new Data_SQLite3 object
     *
     * @param String $hostname not used by SQLite (optional)
     * @param String $username not used by SQLite (optional)
     * @param String $password not used by SQLite (optional)
     */
    public function __construct($hostname = '', $username = '', $password = '')
    {
        // Nothing to do
    }

    /**
     * Connect to a database
     *
     * @param String $db the database name (optional)
     */
    public function connect($database = DB_DATABASE)
    {
        $error = '';
        try
        {
            $this->sqlite_db = new PDO('sqlite:' . $database);
        }
        catch (Exception $exception)
        {
            throw new Exception('Database error: ' . $exception->getMessage());
        }
    }

    /**
     * Disconnect from a database
     */
    public function disconnect()
    {
        $this->sqlite_db = NULL;
    }

    /**
     * Escape a string of SQL so that it's safe to execute
     *
     * @param String $sql the string of SQL to escape
     * @return String safe SQL ready for executing in a query
     */
    public function escape($sql)
    {
        return str_replace('"', '""', $sql);
    }

    /**
     * Execute an SQL query and return a result object fetcher
     *
     * @param String $sql the SQL to execute in the query
     * @return Object a fetcher object with "fetch_object" and "close" methods
     */
    public function query($sql)
    {
        $error = '';
        try
        {
            $result = $this->sqlite_db->query($sql);
            if (!$result) throw new Exception($this->error());
        }
        catch (Exception $exception)
        {
            throw new Exception('Database error: ' . $exception->getMessage());
        }
        return new SQLite3_fetcher($result);
    }

    /**
     * Return the last insert ID after an insert query has been executed
     *
     * @return Integer the last insert ID number
     */
    public function insert_id()
    {
        return $this->sqlite_db->lastInsertId();
    }

    /**
     * Return any error text about the most recent query
     *
     * @return String any error text about the most recent query
     */
    public function error()
    {
        $error_info = $this->sqlite_db->errorInfo();
        return $error_info[2]; // the error message
    }
}

/**
 * Fetch result set rows from a SQLite3 database as objects
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class SQLite3_fetcher extends YAWF
{
    private $result;

    /**
     * Create a new SQLite3_fetcher object
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
        $this->result = NULL;
    }

    /**
     * Fetch the next object from the result set
     *
     * @return Object the next object from the result set, or NULL when done
     */
    public function fetch_object()
    {
        return $this->result->fetchObject();
    }
}

// End of SQLite3.php
