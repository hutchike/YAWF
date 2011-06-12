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
 * Cached data is stored in the cache directory after being
 * encrypted according to its service subclass and object ID
 * which together enable the data to be decrypted to be read.
 *
 * Note: For encryption, please install the Mcrypt extension.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Cache_service extends REST_service
{
    const DEFAULT_EXPIRY_SECS = 10; // 10 seconds

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
        // Look for the service object ID - without it we can't cache

        $id = preg_match('/ id = "(\d+)"/', $params->where, $matches)
            ? $matches[1] : 0;
        if (!$id) return parent::get($params);

        // First look for cached contents

        $data = $this->read_cache($id);
        if ($data) return $data;

        // If not found or expired, write new cached data

        $data = parent::get($params);
        if ($this->cache_secs) $this->write_cache($data, $id);
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
        // We can only cache when there's an object ID

        if (!$params->id) return parent::put($params);

        // Found the ID, so clean the cache

        $this->write_cache(NULL, $params->id);
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
     * Get the cache path by taking the checksum of the passed iddentifier
     *
     * @param Integer $id the ID of the object being cached by the service
     * @return String the cache path (used for testing only)
     */
    protected function get_cache_path_for($id)
    {
        $path = file_exists('app/tmp/cache') ? 'app/tmp/cache' : 'yawf/tmp/cache';
        if (!is_dir($path)) Log::error('No cache folder to write cache data');
        return $path . '/' . md5(get_class($this) . '_' . $id);
    }

    /**
     * Read some contents from the cache, using the first line as an expiry time
     *
     * @param Integer $id the ID of the object being cached by the service
     * @return Array the cached data, or NULL if not found or expired
     */
    protected function read_cache($id)
    {
        $cache_path = $this->get_cache_path_for($id);
        if (!file_exists($cache_path)) return NULL;
        $contents = $this->decrypt(file_get_contents($cache_path), $id);
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
     * @param Integer $id the ID of the object being cached by the service
     */
    protected function write_cache($data, $id)
    {
        $cache_path = $this->get_cache_path_for($id);
        $expires = time() + $this->cache_secs;
        $contents = $expires . "\n" . json_encode($data);
        @file_put_contents($cache_path, $this->encrypt($contents, $id));
        if (isset($php_errormsg)) $this->app->add_error_message($php_errormsg);
    }

    /**
     * Encrypt some data to write to a cache file
     *
     * @param Array $data the data to write to the cache
     * @param Integer $id the ID of the object being cached by the service
     * @return String the encrypted data
     */
    protected function encrypt($data, $id)
    {
        if (!function_exists('mcrypt_encrypt')) return $data;
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->salt($id), $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
    }

    /**
     * Decrypt some data that has been read from a cache file
     *
     * @param Array $data the data to write to the cache
     * @param Integer $id the ID of the object being cached by the service
     * @return String the decrypted data
     */
    protected function decrypt($data, $id)
    {
        if (!function_exists('mcrypt_decrypt')) return $data;
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->salt($id), base64_decode($data), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
    }

    /**
     * Get an encryption salt from an ID nunmber for this service subclass
     *
     * @param Integer $id the ID of the object being cached by the service
     * @return Data the encryption salt
     */
    protected function salt($id)
    {
        return hash("SHA256", get_class($this) . $id, TRUE);
    }
}

// End of Cache.php
