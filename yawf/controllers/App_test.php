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

// See this test controller run at URI "/app_test"

/**
 * The App_test_controller class runs tests over the controllers
 * listed by the APP_TEST_CONTROLLER_LIST constant, and the views
 * listed by the APP_TEST_VIEW_PATH_LIST constant. These tests are
 * run at the URI "/app_test" in your web application.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App_test_controller extends App_controller
{
    private $controllers;
    private $view_paths;

    /**
     * Setup the tests to run by looking at the config constants
     * APP_TEST_CONTROLLER_LIST and APP_TEST_VIEW_PATH_LIST.
     */
    public function setup()
    {
        $this->controllers = split_list(APP_TEST_CONTROLLER_LIST);
        $this->view_paths = split_list(APP_TEST_VIEW_PATH_LIST);
    }

    /**
     * Test the controllers listed in config constant APP_TEST_CONTROLLER_LIST
     * by looking for any failures in each controller's test output.
     */
    public function controllers_test()
    {
        $output = '';
        foreach ($this->controllers as $controller)
        {
            if (strtolower($controller) === 'app') continue; // recursion!
            $output .= uri_get_contents($controller . '_test.part');
        }
        $this->should_not('find failures in test output',
                          preg_match('/failed/', $output) > 0, $output);
    }

    /**
     * Test the view paths listed in config constant APP_TEST_VIEW_PATH_LIST
     * by validating the HTML as well-formatted and legal XHTML.
     */
    public function view_paths_test()
    {
        foreach ($this->view_paths as $view_path)
        {
            $html = uri_get_contents(AppView::uri($view_path));
            $errors = $this->app->xhtml_errors($html);
            $this->should("validate \"$view_path\" as XHTML",
                          !$errors, $errors);
        }
    }
}

// End of App_test.php
