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

load_models('YawfAdmin', 'YawfIssue');

class Admin_controller extends App_controller
{
    protected function create_database()
    {
        // Create a YAWF database, but only do it once

        if (file_exists(Model::get_database())) return;
        $issue = new YawfIssue();
        $sql = file_get_contents('yawf/data/YAWF_' . DB_CONNECTOR . '.sql');
        $issue->query($sql);
    }

    public function before()
    {
        parent::before();
        $this->create_database();
        $this->render->title = 'Admin';
    }

    public function index()
    {
        // TODO
    }

    public function sign_up()
    {
        // TODO
    }

    public function password()
    {
        // TODO
    }

    public function login()
    {
        // TODO
    }

    public function log_out()
    {
        // TODO
    }
}

// End of Admin.php
