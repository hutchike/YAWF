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
    const QUEUE_FOLDER = 'app/tmp/queue';
    const QUEUE_NAME = Symbol::DEFAULT_WORD;

    public static function enqueue($data, $queue = NULL)
    {
        $text = Data::to_serialized($data);
        $file = self::get_filename($text);
        file_put_contents(self::get_folder($queue) . $file, $text);
        return $file;
    }

    public static function dequeue($queue = NULL)
    {
        $folder = self::get_folder($queue);
        $files = array();
        $dir = opendir($folder);
        while ($file = readdir($dir)) $files[] = $file;
        closedir($dir);
        sort($files);
        $files[] = '0';
        do {
            $file = array_shift($files);
        } while (!preg_match('/^\d+/', $file));
        if ($file == '0') return NULL;
        $lockfile = 'lock-' . $file;
        rename($folder . $file, $folder . $lockfile);
        $text = file_get_contents($folder . $lockfile);
        $data = Data::from_serialized($text, TRUE); // no array conversion
        unlink($folder . $lockfile);
        return $data; // as object
    }

    public static function get_folder($queue = NULL)
    {
        $folder = self::QUEUE_FOLDER . '/';
        $folder .= is_null($queue) ? self::QUEUE_NAME : $queue;
        if (!is_dir($folder)) mkdir($folder);
        return $folder . '/';
    }

    public static function get_filename($text)
    {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf("%d.%03d-", $sec, $usec) . md5($text);
    }
}

// End of Queue.php
