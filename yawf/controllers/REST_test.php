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

load_controller('REST');

class REST_test_controller extends Rest_controller
{
    private $test_input;
    private $test_input_map = array(
        'text/xml' => '<xml><list><item>this</item><item>that</item></list></xml>',
        'text/json' => '{"list":{"item":["this","that"]}}',
        'text/yaml' => "list:\n  item:[this, that]",
    );

    // Create a test service with test methods to run checks

    public function render($view = null, $options = array())
    {
        $class = $this->app->get_class_name();
        $service = $class . '_test';
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
        $this->test_method('post', 'text/yaml');
    }

    // Test the "put" method

    public function put_test()
    {
        $this->test_method('put', 'text/xml');
        $this->test_method('put', 'text/json');
        $this->test_method('put', 'text/yaml');
    }

    // Test a method by calling it then looking at the data

    private function test_method($method, $request_type = 'text/xml')
    {
        // Set the request type, and test input data

        $this->server->content_type = $request_type;
        $this->test_input = $this->test_input_map[$request_type];

        // Check we have a service, and call a REST method

        $this->should('have a service', !!$this->service);
        $this->$method();
        $data = $this->render->data;
        $this->should("have called \"$method\"", $data['method'] == $method, $data);

        // Check that test input parsed correctly

        if ($method == 'post' || $method == 'put')
        {
            $parsed = array_key($data, 'data');
            $type = $this->server->content_type;
            $this->should("have parsed \"$type\" input data", $parsed->list->item[1] == 'that');
        }
    }
}

// End of REST_test.php
