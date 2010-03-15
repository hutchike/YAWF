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

class MySQL extends YAWF implements Connector
{
    private $hostname;
    private $username;
    private $password;
    private $dbh;

    public function __construct($hostname = '', $username = '', $password = '')
    {
        $this->hostname = ($hostname ? $hostname : DB_HOSTNAME);
        $this->username = ($username ? $username : DB_USERNAME);
        $this->password = ($password ? $password : DB_PASSWORD);
    }

    public function connect($database = DB_DATABASE)
    {
        $this->dbh = mysql_connect($this->hostname, $this->username, $this->password);
        if (!$this->dbh) throw new Exception('Cannot connect: ' . var_export($this, TRUE));
        mysql_select_db($database, $this->dbh);
    }

    public function disconnect()
    {
        mysql_close($this->dbh);
    }

    public function escape($sql)
    {
        return mysql_real_escape_string($sql);
    }

    public function query($sql)
    {
        $result = mysql_query($sql, $this->dbh);
        $error = $this->error();
        if ($error) throw new Exception("Database error: $error");
        return new MySQL_fetcher($result);
    }

    public function insert_id()
    {
        return mysql_insert_id($this->dbh);
    }

    public function error()
    {
        return mysql_error($this->dbh);
    }
}

class MySQL_fetcher extends YAWF
{
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function close()
    {
        mysql_free_result($this->result);
        $this->result = NULL;
    }

    public function fetch_object()
    {
        return mysql_fetch_object($this->result);
    }
}

// End of MySQL.php
