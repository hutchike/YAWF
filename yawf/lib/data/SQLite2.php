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

class Data_SQLite2 extends YAWF implements Connector
{
    const SQLITE_FILE_PERMISSIONS = 0666; // everyone can read and write
    private $sqlite_db;

    public function __construct($hostname = '', $username = '', $password = '')
    {
        // Nothing to do
    }

    public function connect($database = DB_DATABASE)
    {
        $error = '';
        $this->sqlite_db = new SQLiteDatabase($database, self::SQLITE_FILE_PERMISSIONS, $error);
        if ($error) throw new Exception("Database error: $error");
    }

    public function disconnect()
    {
        $this->sqlite_db = NULL;
    }

    public function escape($sql)
    {
        return str_replace('"', '""', $sql);
    }

    public function query($sql)
    {
        $error = '';
        $result = $this->sqlite_db->query($sql, SQLITE_ASSOC, &$error);
        if ($error) throw new Exception("Database error: $error");
        return new SQLite2_fetcher($result);
    }

    public function insert_id()
    {
        return $this->sqlite_db->lastInsertRowid();
    }

    public function error()
    {
        $error_code = $this->sqlite_db->lastError();
        return $error_code ? sqlite_error_string($error_code) : NULL;
    }
}

class SQLite2_fetcher extends YAWF
{
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function close()
    {
        $this->result = NULL;
    }

    public function fetch_object()
    {
        return $this->result->fetchObject();
    }
}

// End of SQLite2.php
