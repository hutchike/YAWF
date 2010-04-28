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
        $this->render['title'] = 'The YAWF Project';
    }

    public function contact()
    {
        // Nothing to do
    }

    public function cookbook()
    {
        // Nothing to do
    }

    public function download()
    {
        // Nothing to do
    }

    public function faq()
    {
        // Nothing to do
    }

    public function forum()
    {
        // Nothing to do
    }

    public function news()
    {
        // Nothing to do
    }

    public function reference()
    {
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

        $this->render['folder'] = $folder;
        $this->render['parent'] = ($folder != 'yawf' ? dirname($folder) : '');
        $this->render['files'] = $files;
        return array($folder, $parent, $files);
    }

    public function terms()
    {
        // Nothing to do
    }
}

// End of Project.php
