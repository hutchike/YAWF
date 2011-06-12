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

load_service('REST');

/**
 * The Cache_service class allows app services to implement
 * caching by calling a method like "$this->cache_secs(10)".
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Cache_service extends REST_service
{
    const DEFAULT_EXPIRY_SECS = 5; // 5 seconds

    protected $cache_path;
    protected $cache_secs;

    /**
     * Set the default cache seconds to store cached sevice data
     */
    public function __construct()
    {
        $this->cache_secs(self::DEFAULT_EXPIRY_SECS);
    }

    /**
     * Cache the data returned by the "GET" REST method
     *
     * @param Object $params any parameters passed to the service
     * @return Array data to return to the service client
     */
    public function get($params)
    {
        // Look for the service object ID

        $id = preg_match('/ id = "(\d+)"/', $params->where, $matches)
            ? $matches[1] : 0;
        if (!$id) return parent::get($params);
Log::alert('GET ' . get_class($this) . ':' . $id);

        // First look for cached contents

        $this->set_cache_path($id);
        $data = $this->read_cache();
        if ($data) return $data;

        // If not found or expired, write new cached data

        $data = parent::get($params);
        if ($this->cache_secs) $this->write_cache($data);
        return $data;
    }

    /**
     * Uncache the data returned by the "PUT" REST method
     *
     * @param Object $params any parameters passed to the service
     * @return Array data to return to the service client
     */
    public function put($params)
    {
        if (!$params->id) return parent::put($params);

Log::alert('PUT ' . get_class($this) . ':' . $params->id);
        $this->set_cache_path($params->id);
        $this->write_cache(NULL);
        return parent::put($params);
    }

    /**
     * Set the number of seconds to cache the render output
     *
     * @param Integer $secs the number of seconds to cache views for
     */
    protected function cache_secs($secs)
    {
        $this->cache_secs = $secs;
    }
    
    /**
     * Set the number of minutes to cache the render output
     *
     * @param Integer $mins the number of minutes to cache views for
     */
    protected function cache_mins($mins)
    {
        $this->cache_secs = $mins * 60;
    }

    /**
     * Set the number of hours to cache the render output
     *
     * @param Integer $hours the number of hours to cache views for
     */
    protected function cache_hours($hours)
    {
        $this->cache_secs = $hours * 60 * 60;
    }

    /**
     * Set the number of days to cache the render output
     *
     * @param Integer $days the number of days to cache views for
     */
    protected function cache_days($days)
    {
        $this->cache_secs = $days * 60 * 60 * 24;
    }

    /**
     * Set the cache path by taking the checksum of the passed iddentifier
     *
     * @param Integer $id the ID of the object being cached by the service
     * @return String the cache path (used for testing only)
     */
    protected function set_cache_path($id)
    {
        $path = file_exists('app/tmp/cache') ? 'app/tmp/cache' : 'yawf/tmp/cache';
        if (!is_dir($path)) Log::error('No cache folder to write cache data');
        $this->cache_path = $path . '/' . get_class($this) . '_' . $id;
        return $this->cache_path; // for testing
    }

    /**
     * Read some contents from the cache, using the first line as an expiry time
     *
     * @return Array the cached data, or NULL if not found or expired
     */
    protected function read_cache()
    {
        if (!file_exists($this->cache_path)) return NULL;
        $contents = file_get_contents($this->cache_path);
        if (preg_match('/^(\d+)\n(.+)$/s', $contents, $matches))
        {
            $expires = $matches[1];
            $contents = $matches[2];
            if ($expires > time()) return json_decode($contents);
        }
        return NULL;
    }

    /**
     * Write some contents to the cache, using the first line as an expiry time
     *
     * @param Array $data the data to write to the cache
     */
    protected function write_cache($data)
    {
        $expires = time() + $this->cache_secs;
        $contents = $expires . "\n" . json_encode($data);
        @file_put_contents($this->cache_path, $contents);
        if (isset($php_errormsg)) $this->app->add_error_message($php_errormsg);
    }

    /**
     * Clean the cache every hour with a crontab entry like this:
     * 0 * * * * /usr/bin/curl -s www.website.com/folder/clean_cache > /dev/null
     *
     * @param Integer $expiry_secs the maximum age of a cached file in the cache
     */
    protected function clean_cache($expiry_secs = NULL)
    {
        $expiry_secs = first($expiry_secs,
                             Config::get('CACHE_EXPIRY_SECS'),
                             self::DEFAULT_EXPIRY_SECS);
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
