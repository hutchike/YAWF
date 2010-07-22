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

class Cache_controller extends App_controller
{
    protected $cache_path;
    protected $cache_secs;
    protected $cache_options;

    // Render the requested view

    public function render($view = null, $options = array())
    {
        // First look for cached contents

        $this->set_cache_path();
        $contents = $this->read_cache();
        if ($contents) return $contents;

        // If not found or expired, write new cached contents

        $contents = parent::render();
        if ($this->cache_secs) $this->write_cache($contents);
        return $contents;
    }

    // Set the number of seconds to cache the render output

    protected function cache_secs($secs)
    {
        $this->cache_secs = $secs;
    }
    
    // Set the number of minutes to cache the render output

    protected function cache_mins($mins)
    {
        $this->cache_secs = $mins * 60;
    }

    // Set the number of hours to cache the render output

    protected function cache_hours($hours)
    {
        $this->cache_secs = $hours * 60 * 60;
    }

    // Set the number of days to cache the render output

    protected function cache_days($days)
    {
        $this->cache_secs = $days * 60 * 60 * 24;
    }

    // Get/set the cache options

    protected function cache_options($options = NULL)
    {
        if (is_array($options)) $this->cache_options = $options;
        elseif (!is_array($this->cache_options)) $this->cache_options = array();
        return $this->cache_options;
    }

    // Set the cache path by taking the checksum of the full request URI

    protected function set_cache_path($uri = NULL)
    {
        $options = $this->cache_options();
        if (!$uri) $uri = $_SERVER['REQUEST_URI'];
        if (array_key($options, 'no_query')) $uri = preg_replace('/\?.*$/', '', $uri);
        if (array_key($options, 'no_anchor')) $uri = preg_replace('/#.*$/', '', $uri);
        $path = file_exists('app/tmp/cache') ? 'app/tmp/cache' : 'yawf/tmp/cache';
        $this->cache_path = $path . '/' . md5($uri);
        return $this->cache_path; // for testing
    }

    // Read some contents from the cache, using the first line as an expiry time

    protected function read_cache()
    {
        if (!file_exists($this->cache_path)) return NULL;
        $contents = file_get_contents($this->cache_path);
        if (preg_match('/^(\d+)\n(.+)$/s', $contents, $matches))
        {
            $expires = $matches[1];
            $contents = $matches[2];
            if ($expires > time()) return $contents;
        }
        return NULL;
    }

    // Write some contents to the cache, using the first line as an expiry time

    protected function write_cache($contents)
    {
        $expires = time() + $this->cache_secs;
        $contents = $expires . "\n" . $contents;
        file_put_contents($this->cache_path, $contents);
    }

    // Clean the cache every hour with a crontab entry like this:
    // 0 * * * * /usr/bin/curl -s www.website.com/folder/clean_cache > /dev/null

    protected function clean_cache($expiry_secs = CACHE_EXPIRY_SECS)
    {
        $time_now = time();
        $cache_dir = preg_replace('/\w+$/', '', $this->cache_path);
        $dir = opendir($cache_dir);
        while ($cache_file = readdir($dir))
        {
            if (substr($cache_file, 0, 1) === '.') continue;
            $cache_path = $cache_dir . $cache_file;
            $mod_time = filemtime($cache_path);
            $age_secs = $time_now - $mod_time;
            if ($age_secs >= $expiry_secs)
            {
                unlink($cache_path);
                Log::info("Deleted cache file $cache_path at age $age_secs seconds");
            }
        }
        closedir($dir);
    }
}

// End of Cache.php
