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
 * The Remote_test class is used to test the Remote class in the
 * YAWF "lib" folder. The tests are run by the "yash" test files
 * in the "app/tests/lib" folder, which may be started with the
 * simple command "yawf test lib"
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
load_mock('tools', 'CURL');

class Remote_test extends Remote
{
    /**
     * Test we can set and get default values
     */
    public function get_and_set_defaults_test()
    {
        Remote::set_default('pickle', 'gherkin');
        $this->should('set a default value', Remote::get_default('pickle') == 'gherkin');

        Remote::set_default(array('one' => 1, 'two' => 2));
        $this->should('set an array of default values', Remote::get_default('one') == 1 && Remote::get_default('two') == 2);
    }

    /**
     * Test the secure_url() method of the Remote class
     */
    public function secure_url_test()
    {
        // Test that we don't add empty username and password

        $url = 'http://blah.com/whatever';
        $this->set_auth('', '');
        $secure_url = $this->secure_url($url);
        $this->should('leave the URL unchanged when no username and password provided', $secure_url === $url, $secure_url);

        // Test that we do add non-empty username and password to HTTPS URLs

        $url = 'http://blah.com/whatever';
        $this->set_auth('user1', 'pass1');
        $secure_url = $this->secure_url($url);
        $this->should('add a username and password when they are provided for HTTP URLs', $secure_url === 'http://user1:pass1@blah.com/whatever', $secure_url);

        // Test that we do add non-empty username and password to HTTPS URLs

        $url = 'https://blah.com/whatever';
        $this->set_auth('user2', 'pass2');
        $secure_url = $this->secure_url($url);
        $this->should('add a username and password when they are provided for HTTPS URLs', $secure_url === 'https://user2:pass2@blah.com/whatever', $secure_url);
    }

    /**
     * Test the insert() method
     */
    public function insert_test()
    {
        $this->set_returned_data(array(
            'Model' => array('id' => 123),
            'response' => 'ok',
        ));
        $id = $this->insert();
        $this->should('insert with ID 123', $id == 123);
    }

    /**
     * Set the data returned by the CURL mock object
     */
    private function set_returned_data($data)
    {
        CURL::set_returned_content(json_encode($data));
    }
}

// End of Remote.php
