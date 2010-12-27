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

load_service('REST');

/**
 * The REST_test_service class is used to test the REST controller.
 * Basically this class simply returns the data parameters that are
 * passed to each of its REST methods, so they may be tested easily.
 * These methods are each called by the REST_test_controller class.
 * To run the tests, visit an address like http://localhost/REST_test
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class REST_test_service extends REST_service
{
    /**
     * Provide a mock "delete" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function delete($params)
    {
        return $this->test_data_for('delete', $params);
    }

    /**
     * Provide a mock "get" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function get($params)
    {
        return $this->test_data_for('get', $params);
    }

    /**
     * Provide a mock "move" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function move($params)
    {
        return $this->test_data_for('move', $params);
    }

    /**
     * Provide a mock "options" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function options($params)
    {
        return $this->test_data_for('options', $params);
    }

    /**
     * Provide a mock "put" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function put($params)
    {
        return $this->test_data_for('put', $params);
    }

    /**
     * Provide a mock "post" method for testing
     *
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    public function post($params)
    {
        return $this->test_data_for('post', $params);
    }

    /**
     * Return test data for an HTTP method given an HTTP params object
     *
     * @param String $method the HTTP method name (e.g. "get")
     * @param Object $params the HTTP test params to return
     * @return Array an array of HTTP test data for testing
     */
    protected function test_data_for($method, $params)
    {
        return array(
            'method' => $method,
            'params' => $params,
            'input' => $params->input,  // for "put" and "post"
            'data' => $params->data,    // for "put" and "post"
        );
    }
}

// End of REST_test.php
