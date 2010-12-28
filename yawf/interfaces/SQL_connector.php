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
 * All the connector classes should implement this SQL_connector
 * interface to make available methods "connect", "disconnect",
 * "escape", "query", "insert_id" and "error". Any subclass of
 * SQL_model will connect to the database via a SQL_connector.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
interface SQL_connector
{
    /**
     * Connect to a database
     *
     * @param String $db the database name (optional)
     */
    public function connect($db = '');

    /**
     * Disconnect from a database
     */
    public function disconnect();

    /**
     * Escape a string of SQL so that it's safe to execute
     *
     * @param String $sql the string of SQL to escape
     * @return String safe SQL ready for executing in a query
     */
    public function escape($sql);

    /**
     * Execute an SQL query and return a result object fetcher
     *
     * @param String $sql the SQL to execute in the query
     * @return Object a fetcher object with "fetch_object" and "close" methods
     */
    public function query($sql);

    /**
     * Return the last insert ID after an insert query has been executed
     *
     * @return Integer the last insert ID number
     */
    public function insert_id();

    /**
     * Return any error text about the most recent query
     *
     * @return String any error text about the most recent query
     */
    public function error();
}

// End of SQL_connector.php
