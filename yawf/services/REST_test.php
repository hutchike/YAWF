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

// This test service is used when testing the REST controller

load_service('REST');

class REST_test_service extends REST_service
{
    public function delete($params)
    {
        return $this->test_data_for('delete', $params);
    }

    public function get($params)
    {
        return $this->test_data_for('get', $params);
    }

    public function move($params)
    {
        return $this->test_data_for('move', $params);
    }

    public function options($params)
    {
        return $this->test_data_for('options', $params);
    }

    public function put($params)
    {
        return $this->test_data_for('put', $params);
    }

    public function post($params)
    {
        return $this->test_data_for('post', $params);
    }

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
