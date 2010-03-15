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

load_controller('Cache');

// See this test controller run at URL "/cache_test"

class Cache_test_controller extends Cache_controller
{
    const SECS_IN_MIN = 60;
    const MINS_IN_HOUR = 60;
    const HOURS_IN_DAY = 24;
    const TEST_CACHE_SECS = 1;
    const TEST_CONTENTS = 'Just some test contents';

    private $counter;
    
    public function __construct()
    {
        $this->counter = 0;
    }

    // Test that an "example_view" can cache itself for exactly 1 second

    public function render_test()
    {
        $original_view = $this->view;

        $this->set_view('example_view');
        $this->should_not('call the view method yet',
                      $this->counter > 0);

        $this->render();
        $this->should('call the view method now',
                      $this->counter === 1);

        $this->render();
        $this->should_not('call the view method because it is cached',
                      $this->counter > 1);

        sleep(self::TEST_CACHE_SECS);
        $this->render();
        $this->should('call the view method this time because the cache has expired',
                      $this->counter === 2);

        $this->set_view($original_view);
    }

    // Just an "exampe_view" method for the "render_test" above

    protected function example_view()
    {
        $this->cache_secs(self::TEST_CACHE_SECS);
        $this->counter++;
    }

    // Test that we can set the cache secs

    public function cache_secs_test()
    {
        $test_secs = rand(1, 100);
        $this->cache_secs($test_secs);
        $secs = $this->cache_secs;
        $this->should('set cache secs',
                      $test_secs === $secs, array($test_secs, $secs));

        $this->cache_secs(0); // or we'll cache the test results!
    }

    // Test that we can set the cache mins

    public function cache_mins_test()
    {
        $test_mins = rand(1, 100);
        $this->cache_mins($test_mins);
        $mins = $this->cache_secs / self::SECS_IN_MIN;
        $this->should('set cache mins',
                      $test_mins === $mins, array($test_mins, $mins));

        $this->cache_secs(0); // or we'll cache the test results!
    }

    // Test that we can set the cache hours

    public function cache_hours_test()
    {
        $test_hours = rand(1, 100);
        $this->cache_hours($test_hours);
        $hours = $this->cache_secs / self::SECS_IN_MIN / self::MINS_IN_HOUR;
        $this->should('set cache hours',
                      $test_hours === $hours, array($test_hours, $hours));

        $this->cache_secs(0); // or we'll cache the test results!
    }

    // Test that we can set the cache days

    public function cache_days_test()
    {
        $test_days = rand(1, 100);
        $this->cache_days($test_days);
        $days = $this->cache_secs / self::SECS_IN_MIN / self::MINS_IN_HOUR / self::HOURS_IN_DAY;
        $this->should('set cache days',
                      $test_days === $days, array($test_days, $days));

        $this->cache_secs(0); // or we'll cache the test results!
    }

    // Test that the cache options work

    public function cache_options_test()
    {
        $this->cache_options(array());
        $path1 = $this->set_cache_path('/some/test?1');
        $path2 = $this->set_cache_path('/some/test?2');
        $this->should('have different cache paths for different URLs by default',
                      $path1 !== $path2, array($path1, $path2));

        $this->cache_options(array('no_query' => TRUE));
        $path1 = $this->set_cache_path('/some/test?1');
        $path2 = $this->set_cache_path('/some/test?2');
        $this->should('have the same cache paths when we use the "no query" option',
                      $path1 === $path2, array($path1, $path2));

        $this->cache_options(array('no_anchor' => TRUE));
        $path1 = $this->set_cache_path('/some/test#1');
        $path2 = $this->set_cache_path('/some/test#2');
        $this->should('have the same cache paths when we use the "no anchor" option',
                      $path1 === $path2, array($path1, $path2));
    }

    // Test that we can set the cache path

    public function set_cache_path_test()
    {
        $this->set_cache_path();
        list ($yawf_or_app, $cache_folder, $cache_file) = preg_split('/\//', $this->cache_path);
        $this->should('have a cache path in either "yawf" or "app"',
                      in_array($yawf_or_app, array('yawf', 'app')), $yawf_or_app);
        $this->should('set the cache folder to "cache" always',
                      $cache_folder === 'cache', $cache_folder);
        $this->should('use the md5() function to name the cache file',
                      $cache_file === md5($_SERVER['REQUEST_URI']));
    }

    // Test that we can write contents to the cache, then read them back again

    public function read_and_write_cache_test()
    {
        $this->cache_secs(self::TEST_CACHE_SECS);
        $this->write_cache(self::TEST_CONTENTS);
        $contents = $this->read_cache();
        $this->should('read and write the cache',
                      self::TEST_CONTENTS === $contents, $contents);
    }

    // Test that a cached file expires when it should

    public function cache_expires_test()
    {
        // This test must follow the "read_and_write_cache_test()" method

        sleep(self::TEST_CACHE_SECS); // so it expires
        $contents = $this->read_cache();
        $this->should('expire',
                      is_null($contents), $contents);
    }

    // Test that we can clean the cache

    public function clean_cache_test()
    {
        $this->write_cache(self::TEST_CONTENTS);
        $this->clean_cache(0); // delete all cache files
        $contents = $this->read_cache();
        $this->should_not('read anything from a clean cache',
                      self::TEST_CONTENTS === $contents, $contents);
    }
}

// End of Cache_test.php
