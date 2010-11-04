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

class Project_controller extends App_controller
{
    public function before()
    {
        parent::before();
    }

    public function contact()
    {
        // Nothing to do
    }

    public function cookbook()
    {
        $this->render->title = 'Cookbook';
    }

    public function download()
    {
        $this->render->title = 'Download';
    }

    public function faq()
    {
        $this->render->title = 'FAQ';
    }

    public function forum()
    {
        $this->render->title = 'Forum';
    }

    public function news()
    {
        $this->render->title = 'News';
    }

    public function code()
    {
        $this->render->title = 'Code';

        // Get a list of files in the folder

        $folder = $this->params->folder;
        if (!$folder) $folder = 'yawf';
        $dir = opendir($folder);
        $files = array();
        while ($file = readdir($dir))
        {
            if (preg_match('/^\./', $file)) continue;
            if ($folder) $file = "$folder/$file";
            $files[] = $file;
        }
        closedir($dir);

        $this->render->layout = 'purple2';
        $this->render->folder = $folder;
        $this->render->parent = ($folder != 'yawf' ? dirname($folder) : '');
        $this->render->files = $files;
        return (array)$this->render;
    }

    public function terms()
    {
        $this->render->title = 'Terms &amp; Conditions';
    }
}

// End of Project.php
