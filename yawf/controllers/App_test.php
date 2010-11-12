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

// See this test controller run at URL "/app_test"

class App_test_controller extends App_controller
{
    private $controllers;
    private $view_paths;

    public function setup()
    {
        $this->controllers = split_list(APP_TEST_CONTROLLER_LIST);
        $this->view_paths = split_list(APP_TEST_VIEW_PATH_LIST);
    }

    public function controllers_test()
    {
        $output = '';
        foreach ($this->controllers as $controller)
        {
            if (strtolower($controller) === 'app') continue; // recursion!
            $output .= url_get_contents('/' . $controller . '_test.part');
        }
        $this->should_not('find failures in test output',
                          preg_match('/failed/', $output) > 0, $output);
    }

    public function view_paths_test()
    {
        foreach ($this->view_paths as $view_path)
        {
            $html = url_get_contents(AppView::url($view_path));
            $errors = $this->app->xhtml_errors($html);
            $this->should("validate \"$view_path\" as XHTML",
                          !$errors, $errors);
        }
    }
}

// End of App_test.php
