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

load_controller('REST');

class REST_test_controller extends Rest_controller
{
    private $test_input;
    private $test_input_map = array(
        'text/xml' => '<root><assoc><list>this</list><list>that</list></assoc></root>',
        'text/json' => '{"assoc":{"list":["this","that"]}}',
        'text/serialized' => 'a:1:{s:5:"assoc";a:1:{s:4:"list";a:2:{i:0;s:4:"this";i:1;s:4:"that";}}}',
        'text/yaml' => "assoc:\n  list:[this, that]",
    );

    // Create a test service with test methods to run checks

    public function render($view = null, $options = array())
    {
        $service = ucfirst($this->app->get_folder()) . '_test';
        $this->service = $this->app->new_service($service);
        return App_controller::render(); // to show test results
    }

    // Override the "get_input" method for testing

    protected function get_input()
    {
        return $this->test_input;
    }

    // Test the "delete" method

    public function delete_test()
    {
        $this->test_method('delete');
    }

    // Test the "get" method

    public function get_test()
    {
        $this->test_method('get');
    }

    // Test the "move" method

    public function move_test()
    {
        $this->test_method('move');
    }

    // Test the "options" method

    public function options_test()
    {
        $this->test_method('options');
    }

    // Test the "post" method

    public function post_test()
    {
        $this->test_method('post', 'text/xml');
        $this->test_method('post', 'text/json');
        $this->test_method('post', 'text/serialized');
        $this->test_method('post', 'text/yaml');
    }

    // Test the "put" method

    public function put_test()
    {
        $this->test_method('put', 'text/xml');
        $this->test_method('put', 'text/json');
        $this->test_method('put', 'text/serialized');
        $this->test_method('put', 'text/yaml');
    }

    // Test a method by calling it then looking at the returned data

    private function test_method($method, $request_type = 'text/xml')
    {
        // Set the request type, and test input data

        $this->server->content_type = $request_type;
        $this->test_input = $this->test_input_map[$request_type];

        // Check we're using the REST_test_service

        $this->should('have a REST test service', $this->service instanceof REST_test_service, get_class($this->service));

        // Call the service method and check data

        $this->$method();
        $data = $this->render->data;
        $this->should("have called \"$method\"", $data['method'] == $method, $data);

        // Check that test input parsed correctly

        if ($method == 'post' || $method == 'put')
        {
            $parsed = array_key($data, 'data');
            $type = $this->server->content_type;
            $this->should("have parsed \"$type\" input data using \"$method\"", $parsed['assoc']['list'][1] == 'that');
        }
    }
}

// End of REST_test.php
