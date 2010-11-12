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

class Data_SQLite3 extends YAWF implements Connector
{
    private $sqlite_db;

    public function __construct($hostname = '', $username = '', $password = '')
    {
        // Nothing to do
    }

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

    public function insert_id()
    {
        return $this->sqlite_db->lastInsertId();
    }

    public function error()
    {
        $error_info = $this->sqlite_db->errorInfo();
        return $error_info[2]; // the error message
    }
}

class SQLite3_fetcher extends YAWF
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

// End of SQLite3.php
