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

class Test_controller extends App_controller
{
    const SCRIPT_RUNNER = 'runner.js';
    const SCRIPT_PATH = 'app/scripts/test';
    protected $url;
    protected $script;

    public function before()
    {
        $this->url = $this->render['url'] = default_value($this->params->url, '/');
        $this->script = $this->render['script'] = default_value($this->params->script, self::SCRIPT_RUNNER);
        $this->render['flash'] = '';
    }

    public function runner()
    {
        if (!TESTING_ENABLED) $this->app->redirect('', TRUE);

        if ($this->app->get_content_type() != 'part')
        {
            $params = 'url=' . $this->url . '&script=' . $this->script;
            $this->app->redirect('test/runner.part?' . $params, TRUE);
        }
    }

    public function logger()
    {
        $lines = preg_split('/\n/', html_entity_decode($this->params->lines));
        $count = 0;
        foreach ($lines as $line)
        {
            if ($line)
            {
                Log::test($line);
                $count++;
            }
        }
        $this->render['count_lines_logged'] = $count;
    }

    public function browser()
    {
        $scripts = array();
        $dir = opendir(self::SCRIPT_PATH);
        while ($script = readdir($dir))
        {
            if (preg_match('/\.js$/', $script)) $scripts[] = $script;
        }
        closedir($dir);
        $this->render['scripts'] = $scripts;

        $file_path = self::SCRIPT_PATH . '/' . $this->script;
        if (!file_exists($file_path))
            $this->render['flash'] = 'Test script "' . $file_path . '" does not exist?';

        if (!count($scripts))
            $this->render['flash'] = 'Folder "' . self::SCRIPT_PATH . '" is empty?';
    }

    public function console()
    {
        // Nothing to do
    }

    public function script()
    {
        $this->render['file_path'] = self::SCRIPT_PATH . '/' . $this->script;
    }
}

// End of Test.php