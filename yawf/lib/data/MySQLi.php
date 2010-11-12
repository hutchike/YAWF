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

class Data_MySQLi extends YAWF implements Connector
{
    private $hostname;
    private $username;
    private $password;
    private $mysqli;

    public function __construct($hostname = '', $username = '', $password = '')
    {
        $this->hostname = ($hostname ? $hostname : DB_HOSTNAME);
        $this->username = ($username ? $username : DB_USERNAME);
        $this->password = ($password ? $password : DB_PASSWORD);
    }

    public function connect($database = DB_DATABASE)
    {
        $this->mysqli = new mysqli($this->hostname, $this->username, $this->password, $database);
        if (mysqli_connect_errno()) throw new Exception('Cannot connect: ' . var_export($this, TRUE));
    }

    public function disconnect()
    {
        $this->mysqli->close();
    }

    public function escape($sql)
    {
        return $this->mysqli->real_escape_string($sql);
    }

    public function query($sql)
    {
        $result = $this->mysqli->query($sql);
        $error = $this->error();
        if ($error) throw new Exception("Database error: $error");
        return $result;
    }

    public function insert_id()
    {
        return $this->mysqli->insert_id;
    }

    public function error()
    {
        return $this->mysqli->error;
    }
}

// End of MySQLi.php
