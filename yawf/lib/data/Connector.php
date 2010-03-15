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

interface Connector
{
    public function connect($db = '');  // connect to a database
    public function disconnect();       // disconnect from a database
    public function escape($sql);       // escape some dangerous SQL
    public function query($sql);        // run an SQL query statement
    public function insert_id();        // return the last insert ID
    public function error();
}

// End of Connector.php
