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
 * Connect to a SQLite2 database via the older SQLite PHP extension.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Data_SQLite2 extends YAWF implements Connector
{
    const SQLITE_FILE_PERMISSIONS = 0666; // everyone can read and write
    private $sqlite_db;

    /**
     * Create a new Data_SQLite2 object
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
        $this->sqlite_db = new SQLiteDatabase($database, self::SQLITE_FILE_PERMISSIONS, $error);
        if ($error) throw new Exception("Database error: $error");
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
        $result = $this->sqlite_db->query($sql, SQLITE_ASSOC, $error);
        if ($error) throw new Exception("Database error: $error");
        return new SQLite2_fetcher($result);
    }

    /**
     * Return the last insert ID after an insert query has been executed
     *
     * @return Integer the last insert ID number
     */
    public function insert_id()
    {
        return $this->sqlite_db->lastInsertRowid();
    }

    /**
     * Return any error text about the most recent query
     *
     * @return String any error text about the most recent query
     */
    public function error()
    {
        $error_code = $this->sqlite_db->lastError();
        return $error_code ? sqlite_error_string($error_code) : NULL;
    }
}

/**
 * Fetch result set rows from a SQLite2 database as objects
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class SQLite2_fetcher extends YAWF
{
    private $result;

    /**
     * Create a new SQLite2_fetcher object
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

// End of SQLite2.php
