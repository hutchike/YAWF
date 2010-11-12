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

load_controller('Admin');

class Admin_test_controller extends Admin_controller
{
    public function setup()
    {
        $admin = new YawfAdmin();
        $this->database_filename = $admin->get_database();
    }

    public function teardown()
    {
        if (file_exists($this->database_filename))
        {
            unlink($this->database_filename);
        }
    }

    public function before()
    {
        // Do nothing (instead of automatically creating a database)
    }

    public function create_database_test()
    {
        $admin = new YawfAdmin();

        $test_db_exists = file_exists($this->database_filename);
        $this->should_not('have a test database already', $test_db_exists,
                          $admin->get_database());

        parent::create_database();

        $test_db_exists = file_exists($this->database_filename);
        $this->should('have a test database now', $test_db_exists,
                      $admin->get_database());

    }

    public function index_test()
    {
        // TODO
    }
}

// End of Admin_test.php
