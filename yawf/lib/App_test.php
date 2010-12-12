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

require_once 'lib/App.php';

/**
 * The YAWF App_test class adds unit testing methods to the standard
 * App class.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App_test extends App
{
    protected $test_run;

    /**
     * Construct a new App_test object
     *
     * @param String $uri an optional relative URI (e.g. "/folder/file")
     */
    public function __construct($uri = NULL)
    {
        parent::__construct($uri);
        if (TESTING_ENABLED !== TRUE) parent::redirect('', array('exit' => TRUE));
        $this->reset_folder();
        $this->test_run = NULL;

        // Models connect to the test database

        $model = new Model();
        $model->set_connector(DB_CONNECTOR,
                              array(Symbol::DATABASE => DB_DATABASE_TEST));
        $this->is_testing = TRUE;
        Log::type(Symbol::TEST);

        // Translations need validating

        Translate::validate();
    }

    /**
     * Remove "_test" from the folder
     */
    protected function reset_folder()
    {
        $this->folder = preg_replace('/_test$/', '', $this->folder);
    }

    /**
     * It's important that tests cannot make redirects
     *
     * @param String $uri the URI that would normally be shown on redirection
     * @param Array $options an optional array of options (ignored for testing)
     */
    public function redirect($uri, $options = array())
    {
        // Do nothing
    }

    /**
     * Variation of the regular "render_view" that can insert test results
     *
     * @param String $file blah
     * @param Object $render data to render in the view
     * @param Array $options an array of rendering options (optional)
     * @return String the contents to send in response to the client
     */
    public function render_view($file, $render = NULL, $options = array())
    {
        $render = new Object($render);
        if (array_key($options, Symbol::FOLDER) === 'types' &&
            array_key($_SERVER, 'REMOTE_ADDR')) // to check it's a web request
            $this->render_test_run($render);

        return parent::render_view($file, $render, $options);
    }

    /**
     * Render the test run by modifying a render object to include the results
     *
     * @param Object $render the render object to modify with test results
     */
    protected function render_test_run($render)
    {
        if ($this->test_run) return; // so we don't repeat the tests
        $testee = (defined('REST_SERVICE_LIST') && in_array($this->folder, split_list(REST_SERVICE_LIST)) ? $this->service : $this->controller);
        $test_run = $this->test_run = new TestRun($testee);
        $test_run->run_tests();
        $render->test_run_output = $test_run->get_output();
        $render->test_cases = $test_run->get_test_cases();
        $render->count_passed = $test_run->count_test_cases_that_passed();
        $render->count_failed = $test_run->count_test_cases_that_failed();
        $render->testee_name = $test_run->get_testee_name();
        $render->title = 'Testing "' . $test_run->get_testee_name() . '"';
        $render->content = parent::render_view('test_run', $render, array(Symbol::FOLDER => Symbol::TEST));
    }

    /**
     * The testing controller runs test cases by calling this method on its $app
     *
     * @param String $desc a description of the test
     * @param Boolean $passed whether the test passed
     * @param Object $test_data the data used in the test (optional)
     * @param String $method the method that is running tests (optional)
     */
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

    /**
     * Validate some HTML as XHTML and return any errors
     *
     * @param String $html the HTML to validate as XHTML
     * @return Array an array of validation errors, or NULL
     */
    public function xhtml_errors($html)
    {
        // Create an XHTML validator

        load_plugin('Validators/XhtmlValidator');
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

/**
 * A class to hold details about a test run for a Controller or Service object
 */
class TestRun
{
    private $testee;            // the testee controller or service object
    private $test_cases;        // the array of arrays of TestCase objects
    private $test_output;       // string of test output from the test run

    /**
     * Create a new TestRun object
     *
     * @param Object $testee a Controller or Service object to test
     */
    public function __construct($testee)
    {
        $this->testee = $testee;
        $this->test_cases = array();
        $this->test_output = '';
        Log::test('testing: ' . $this->get_testee_name());
    }

    /**
     * Run test methods on the testee object (i.e. methods ending in "_test").
     * Note that the "setup" and "teardown" methods are called when available.
     */
    public function run_tests()
    {
        $methods = get_class_methods($this->testee);
        if (in_array('setup', $methods)) $this->testee->setup();
        foreach ($methods as $test_method)
        {
            // Only run methods on testee that end with "_test"

            if (!preg_match('/_test$/', $test_method)) continue;
            try
            {
                $this->add_method( $test_method );
                $this->add_output( $this->testee->$test_method() );
            }
            catch (Exception $e)
            {
                $should = 'handle exception "' . $e->getMessage() . '"';
                $this->add_test_case($should, FALSE, $e, $test_method);
            }
        }
        if (in_array('teardown', $methods)) $this->testee->teardown();
    }

    /**
     * Get the name of the testee object (with any "_test" suffix removed)
     *
     * @return String the name of the testee object (a Controller or Service)
     */
    public function get_testee_name()
    {
        return preg_replace('/_test/', '', get_class($this->testee));
    }

    /**
     * Add some output to the test output that will be displayed in the results
     *
     * @param String $output the output to append to the test output
     */
    public function add_output($output)
    {
        $this->test_output .= $output;
    }

    /**
     * Return the output from the tests that have been run
     *
     * @return String the output from the tests that have been run
     */
    public function get_output()
    {
        return $this->test_output;
    }
    
    /**
     * Return an array of all the test cases in this test run
     *
     * @return Array an array of all the test cases in this test run
     */
    public function get_test_cases()
    {
        return $this->test_cases;
    }

    /**
     * Add a test method to the array of test cases for this test run
     *
     * @param String $test_method the test method to add to the test cases
     */
    public function add_method($test_method)
    {
        $this->test_cases[$test_method] = array();
        Log::test('method: ' . $test_method . '()');
    }

    /**
     * Add a test case to the array of test cases for this test method
     *
     * @param String $desc a description of the test case
     * @param Boolean $passed whether the test case passed
     * @param Object $test_data test data used in the test
     * @param String $method the name of the testing method
     */
    public function add_test_case($desc, $passed, $test_data, $method)
    {
        $this->test_cases[$method][] = new TestCase($desc, $passed, $test_data);
        Log::test(($passed ? 'passed' : 'failed') . ': Should ' . $desc);
        if ($passed === FALSE) Log::test('data: ' . dump($test_data));
    }

    /**
     * Return a count of test results after applying a filter
     *
     * @param String $filter the filter applied (e.g. "all", "failed", "passed")
     * @return Integer a count of the test cases matching the filter
     */
    private function filter_test_cases($filter = 'all')
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

    /**
     * Return a count of all the test cases
     *
     * @return Integer a count of all the test cases
     */
    public function count_test_cases()
    {
        return $this->filter_test_cases();
    }

    /**
     * Return a count of all the test cases that passed
     *
     * @return Integer a count of all the test cases that passed
     */
    public function count_test_cases_that_passed()
    {
        return $this->filter_test_cases('passed');
    }

    /**
     * Return a count of all the test cases that failed
     *
     * @return Integer a count of all the test cases that failed
     */
    public function count_test_cases_that_failed()
    {
        return $this->filter_test_cases('failed');
    }
}

/**
 * A class to hold details about a particular test case
 */
class TestCase
{
    private $desc;      // a string to describe the test case
    private $passed;    // a boolean, true if the case passes
    private $data;      // mixed test data, shown if it fails

    /**
     * Create a new TestCase object
     *
     * @param String $desc a description of the test case
     * @param Boolean $passed whether the test case passed (FALSE by default)
     * @param Object $data test data used to perform the test (optional)
     */
    public function __construct($desc, $passed = FALSE, $data = NULL)
    {
        $this->desc = $desc;
        $this->passed = $passed;
        $this->data = $data;
    }

    /**
     * Return the description of the test case
     *
     * @return String the test case description
     */
    public function get_desc()
    {
        return $this->desc;
    }

    /**
     * Return whether the test case passed
     *
     * @return Boolean whether the test case passed
     */
    public function passed()
    {
        return $this->passed;
    }

    /**
     * Return whether the test case failed
     *
     * @return Boolean whether the test case failed
     */
    public function failed()
    {
        return !$this->passed;
    }

    /**
     * Return the test case data as text to be displayed in the results
     *
     * @return String a text representation of the test case data
     */
    public function get_data_as_text()
    {
        return var_export($this->data);
    }
}

// End of App_test.php
