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

load_tool('Data');

/**
 * Provide a simple helper to write objects to a queue and read
 * them from the queue (either by blocking - default behavior -
 * or non-blocking).
 *
 * The objects written and read from the queue retain their PHP
 * object classes (provided classes are already loaded into the
 * program). The objects are written as files in queue folders.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Queue extends YAWF
{
    const QUEUE_FOLDER = 'app/tmp/queue';
    const QUEUE_PAUSE = 1; // second when blocking
    const QUEUE_NAME = Symbol::DEFAULT_WORD;

    /**
     * Enqueue an object on a queue
     *
     * @param Object $object the object to enqueue
     * @param String $queue the queue name (optional)
     * @return String the filename of the written object
     */
    public static function enqueue($object, $queue = NULL)
    {
        $text = Data::to_serialized($object);
        $file = self::get_filename($text);
        file_put_contents(self::get_folder($queue) . $file, $text);
        return $file;
    }

    /**
     * Dequeue an object from a queue without blocking
     *
     * @param String $queue the name of the queue (optional)
     * @return Object the next object in the queue
     */
    public static function dequeue_nb($queue = NULL)
    {
        return self::dequeue($queue, FALSE); // don't block
    }

    /**
     * Dequeue an object from a queue (and assume we're blocking)
     *
     * @param String $queue the name of the queue (optional)
     * @param Bool $is_blocking whether to block (optional)
     * @return Object the next object in the queue
     */
    public static function dequeue($queue = NULL, $is_blocking = TRUE)
    {
        // Get the file to dequeue

        $folder = self::get_folder($queue);
        $file = self::get_next_file($folder, $is_blocking);
        if (is_null($file)) return NULL;

        // Lock and read the file

        $lockfile = 'lock-' . $file;
        rename($folder . $file, $folder . $lockfile);
        $text = file_get_contents($folder . $lockfile);
        $object = Data::from_serialized($text, TRUE); // no array conversion

        // Remove the file and return the data

        unlink($folder . $lockfile);
        return $object;
    }

    /**
     * Get a folder path for a queue name
     *
     * @param String $queue the name of the queue
     * @return String the folder path to write queue files
     */
    private static function get_folder($queue = NULL)
    {
        $folder = self::QUEUE_FOLDER . '/';
        $folder .= is_null($queue) ? self::QUEUE_NAME : $queue;
        if (!is_dir($folder)) mkdir($folder);
        return $folder . '/';
    }

    /**
     * Get a filename for an object serialized as text
     *
     * @param String $text the object serialized as text
     * @return String the filename of the serialized object
     */
    private static function get_filename($text)
    {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf("%d.%03d-", $sec, $usec) . md5($text);
    }

    /**
     * Get the next file from a folder, optionally blocking
     *
     * @param String $folder the folder to read
     * @param Bool $is_blocking option to block for a file
     * @return String the filename of the next file
     */
    private static function get_next_file($folder, $is_blocking = FALSE)
    {
        while (TRUE) // assume we're blocking
        {
            // Look for a file

            $files = array();
            $dir = opendir($folder);
            while ($file = readdir($dir)) $files[] = $file;
            closedir($dir);
            sort($files);
            $files[] = '0'; // a final match
            do {
                $file = array_shift($files);
            } while (!preg_match('/^\d+/', $file));

            // Return a file, null or block

            if ($file != '0') return $file;
            if (!$is_blocking) return NULL;
            sleep(self::QUEUE_PAUSE);
        }
    }
}

// End of Queue.php
