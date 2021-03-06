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

Log::type('test');

/**
 * The Test_controller class displays the web page GUI test framework
 * so that JavaScript tests may be run to check the website usability.
 * See your "app/public/scritps/test/runner.js" file to add some tests.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Test_controller extends App_controller
{
    const SCRIPT_RUNNER = 'runner.js';
    const SCRIPT_DIR = 'app/public/scripts/test';
    protected $url;
    protected $script;

    /**
     * Setup render data for the website testing framework
     */
    public function before()
    {
        $this->url = $this->render->url = first($this->params->url, '/');
        $this->script = $this->render->script = first($this->params->script, self::SCRIPT_RUNNER);
        $this->render->error = '';
    }

    /**
     * Redirect to "test/runner.part" to ensure the app layout is not rendered
     */
    public function runner()
    {
        if (!TESTING_ENABLED) $this->app->redirect('', array('exit' => TRUE));

        if ($this->app->get_content_type() != 'part')
        {
            $params = 'url=' . $this->url . '&script=' . $this->script;
            $this->app->redirect('test/runner.part?' . $params, array('exit' => TRUE));
        }
    }

    /**
     * Log all the lines passed in the HTTP param "lines" into the test log
     */
    public function logger()
    {
        $lines = preg_split('/\n/', html_entity_decode($this->params->lines));
        $count = 0;
        foreach ($lines as $line)
        {
            if ($line = trim($line))
            {
                Log::test($line);
                $count++;
            }
        }
        $this->render->count_lines_logged = $count;
    }

    /**
     * Display the "test/browser" view to select a web page to test
     */
    public function browser()
    {
        $scripts = array();
        $dir = opendir(self::SCRIPT_DIR);
        while ($script = readdir($dir))
        {
            if (preg_match('/\.js$/', $script)) $scripts[] = $script;
        }
        closedir($dir);
        $this->render->scripts = $scripts;

        $file_path = self::SCRIPT_DIR . '/' . $this->script;
        if (!file_exists($file_path))
            $this->render->error = 'Test script "' . $file_path . '" does not exist?';

        if (!count($scripts))
            $this->render->error = 'Folder "' . self::SCRIPT_DIR . '" is empty?';
    }

    /**
     * Display the "test/console" view to display test passes and failures
     */
    public function console()
    {
        // Nothing to do
    }

    /**
     * Display a JavaScript testing script in the main "view" frame
     */
    public function script()
    {
        $this->render->file_path = self::SCRIPT_DIR . '/' . $this->script;
    }

    /**
     * Don't create a flash object because it overwrites that of the tested page
     *
     * @return NULL no flash object
     */
    protected function flash_object()
    {
        return NULL; // so we don't affect the tested controller's flash object
    }
}

// End of Test.php
