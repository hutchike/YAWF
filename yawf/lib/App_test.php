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

require_once 'lib/App.php';

class App_test extends App
{
    protected $test_run;

    // Construct a new App_test object

    public function __construct()
    {
        parent::__construct();
        if (TESTING_ENABLED !== TRUE) parent::redirect('', TRUE); // exit!
        $this->reset_folder();
        $this->test_run = NULL;

        // Models connect to the test database

        Model::set_database(DB_DATABASE_TEST);
        $this->is_testing = TRUE;
    }

    // Remove "_test" from the folder

    protected function reset_folder()
    {
        $this->folder = preg_replace('/_test$/', '', $this->folder);
    }

    // It's important that tests cannot redirect!

    public function redirect($url, $exit = FALSE)
    {
        // Do nothing
    }

    // A variation of the regular "render_view" that will insert test results

    public function render_view($file, $render = array(), $options = array())
    {
        if (array_key($options, 'folder') === 'types')
            $this->render_test_run($render);

        return parent::render_view($file, $render, $options);
    }

    // Render the test run to show test results

    protected function render_test_run(&$render)
    {
        if ($this->test_run) return; // so we don't repeat the tests
        $test_run = $this->test_run = new TestRun($this->controller);
        $test_run->run_tests();
        $render['test_run_output'] = $test_run->get_output();
        $render['test_cases'] = $test_run->get_test_cases();
        $render['count_passed'] = $test_run->count_test_cases_that_passed();
        $render['count_failed'] = $test_run->count_test_cases_that_failed();
        $render['controller_name'] = $test_run->get_controller_name();
        $render['title'] = 'Testing "' . $test_run->get_controller_name() . '"';
        $render['content'] = parent::render_view('test_run', $render, array('folder' => 'test'));
    }

    // The testing controller runs test cases by calling this method on its $app

    public function test_case($desc, $passed, $test_data = NULL, $method = NULL)
    {
        if (!is_bool($passed))
        {
            throw new Exception("Test case $desc needs a TRUE/FALSE assertion");
        }

        if (!$method)
        {
            $trace = debug_backtrace();
            $method = $trace[2]['function'];
        }

        $this->test_run->add_test_case($desc, $passed, $test_data, $method);
    }

    // Validate some HTML as XHTML and return any errors

    public function xhtml_errors($html)
    {
        // Create an XHTML validator

        load_plugin('XhtmlValidator');
        $xhtml_validator = new XhtmlValidator();

        // Validate the HTML as XHTML

        $is_valid = $xhtml_validator->validate($html);
        if ($is_valid) return NULL;

        // Filter out unhelpful errors

        $errors = $xhtml_validator->getErrors();
        $filtered = array();
        foreach ($errors as $error)
        {
            $error = strip_tags($error);
            if (preg_match('/^Tag script may not contain raw character data/', $error)) continue;
            $filtered[] = $error;
        }

        // Return the filtered errors

        return count($filtered) ? $filtered : NULL;
    }
}

// A class with details about a test run for the "test/test_run" view

class TestRun
{
    private $controller;        // the test controller with test methods
    private $test_cases;        // an array of arrays of TestCase objects
    private $test_output;       // string of test output from the test run

    public function __construct($test_controller)
    {
        $this->controller = $test_controller;
        $this->test_cases = array();
        $this->test_output = '';
        Log::test('controller: ' . $this->get_controller_name());
    }

    public function run_tests()
    {
        $methods = get_class_methods($this->controller);
        if (in_array('set_up', $methods)) $this->controller->set_up();
        foreach ($methods as $test_method)
        {
            // Only run controller methods that end with "_test"

            if (!preg_match('/_test$/', $test_method)) continue;
            try
            {
                $this->add_method( $test_method );
                $this->add_output( $this->controller->$test_method() );
            }
            catch (Exception $exception)
            {
                $should = 'handle exception "' . $exception->getMessage() . '"';
                $this->add_test_case($should, FALSE, $exception, $test_method);
            }
        }
        if (in_array('tear_down', $methods)) $this->controller->tear_down();
    }

    public function get_controller_name()
    {
        return preg_replace('/_test/', '', get_class($this->controller));
    }

    public function add_output($output)
    {
        $this->test_output .= $output;
    }

    public function get_output()
    {
        return $this->test_output;
    }
    
    public function get_test_cases()
    {
        return $this->test_cases;
    }

    public function add_method($test_method)
    {
        $this->test_cases[$test_method] = array();
        Log::test('method: ' . $test_method . '()');
    }

    public function add_test_case($desc, $passed, $test_data, $method)
    {
        $this->test_cases[$method][] = new TestCase($desc, $passed, $test_data);
        Log::test(($passed ? 'passed' : 'failed') . ': Should ' . $desc);
        if ($passed === FALSE) Log::test('data: ' . var_export($test_data, TRUE));
    }

    private function _count_test_cases($filter = 'all')
    {
        $count = 0;
        foreach ($this->test_cases as $method => $cases)
        {
            foreach ($cases as $case)
            {
                if ($filter === 'all') $count++;
                elseif ($filter === 'passed' && $case->passed()) $count++;
                elseif ($filter === 'failed' && $case->failed()) $count++;
            }
        }
        return $count;
    }

    public function count_test_cases()
    {
        return $this->_count_test_cases();
    }

    public function count_test_cases_that_passed()
    {
        return $this->_count_test_cases('passed');
    }

    public function count_test_cases_that_failed()
    {
        return $this->_count_test_cases('failed');
    }
}

// A class with details about a test case for the "test/test_run" view

class TestCase
{
    private $desc;      // a string to describe the test case
    private $passed;    // a boolean, true if the case passes
    private $data;      // mixed test data, shown if it fails

    public function __construct($desc, $passed = FALSE, $data = NULL)
    {
        $this->desc = $desc;
        $this->passed = $passed;
        $this->data = $data;
    }

    public function get_desc()
    {
        return $this->desc;
    }

    public function passed()
    {
        return $this->passed;
    }

    public function failed()
    {
        return !$this->passed;
    }

    public function get_data_as_text()
    {
        return var_export($this->data);
    }
}

// End of App_test.php