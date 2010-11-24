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

load_helper('Data');

class Queue extends YAWF
{
    const QUEUE_FOLDER = 'app/tmp/queue/';

    public static function enqueue($data)
    {
        $text = Data::to_serialized($data);
        $file = self::get_filename($text);
        file_put_contents(self::QUEUE_FOLDER . $file, $text);
        return $file;
    }

    public static function dequeue()
    {
        $files = array();
        $dir = opendir(self::QUEUE_FOLDER);
        while ($file = readdir($dir)) $files[] = $file;
        closedir($dir);
        sort($files);
        $files[] = '0';
        do {
            $file = array_shift($files);
        } while (!preg_match('/^\d+/', $file));
        if ($file == '0') return NULL;
        $lockfile = 'lock-' . $file;
        rename(self::QUEUE_FOLDER . $file, self::QUEUE_FOLDER . $lockfile);
        $data = self::read($lockfile);
        unlink(self::QUEUE_FOLDER . $lockfile);
        return $data;
    }

    public static function read($file)
    {
        $text = file_get_contents(self::QUEUE_FOLDER . $file);
        return Data::from_serialized($text, TRUE); // no array conversion
    }

    public static function get_filename($text)
    {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf("%d.%03d-", $sec, $usec) . md5($text);
    }
}

// End of Queue.php
