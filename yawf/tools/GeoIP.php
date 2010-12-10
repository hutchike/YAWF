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

/**
 * This helper wraps the "Net/GeoIP" plugin from MaxMind with a
 * simple object-oriented interface to provide the IP address,
 * the country name and code, and the location (i.e. city).
 *
 * For example, if you're using the GeoIPCity.dat data file:
 *
 * <code>
 * load_tool('GeoIP');
 * $geo = new GeoIP('app/data/GeoIPCity.dat');
 * $country = $geo->country_name();
 * $code = $geo->country_code();
 * $city = $geo->city();
 * $rgn = $geo->region();
 * $zip = $geo->zip_code();
 * $dma = $geo->dma_code();
 * $lat = $geo->latitude();
 * $long = $geo->longitude();
 * </code>
 *
 * ...or to use the default GeoIP.dat file in /usr/share/GeoIP:
 *
 * <code>
 * load_tool('GeoIP');
 * $geo = new GeoIP('app/data/GeoIPCity.dat');
 * $country = $geo->country_name();
 * $code = $geo->country_code();
 * // Can't use the city data methods
 * // coz they will just return NULL
 * </code>
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class GeoIP
{
    const DEFAULT_FILENAME = '/usr/share/GeoIP/GeoIP.dat';
    private static $locations = array();
    private $geoip;

    /**
     * Create a GeoIP wrapper object
     *
     * @param String $filename the filename of the GeoIP data file (optional)
     * @return GeoIP a new GeoIP wrapper object
     */
    public function __construct($filename = self::DEFAULT_FILENAME)
    {
        load_plugin('Net/GeoIP');
        $this->geoip = Net_GeoIP::getInstance($filename);
    }

    /**
     * Return the country name for an IP address
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the country name for the IP address
     */
    public function country_name($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        $name = $this->location_field('countryName', $client_ip);
        if (is_null($name)) $name = $this->geoip->lookupCountryName($client_ip);
        return $name;
    }

    /**
     * Return the country code (2 letters) for an IP address
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the country code for the IP address
     */
    public function country_code($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        $code = $this->location_field('countryCode', $client_ip);
        if (is_null($code)) $code = $this->geoip->lookupCountryCode($client_ip);
        return $code;
    }

    /**
     * Return the city name for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the city name for the IP address
     */
    public function city($client_ip = NULL)
    {
        return $this->location_field('city', $client_ip);
    }

    /**
     * Return the region for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the region (e.g. "CA") the IP address
     */
    public function region($client_ip = NULL)
    {
        return $this->location_field('region', $client_ip);
    }

    /**
     * Return the zip code for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the zip code for the IP address
     */
    public function zip_code($client_ip = NULL)
    {
        return $this->location_field('postalCode', $client_ip);
    }

    /**
     * Return the DMA (Direct Marketing Association) code for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the DMA code for the IP address
     */
    public function dma_code($client_ip = NULL)
    {
        return $this->location_field('dmaCode', $client_ip);
    }

    /**
     * Return the telephone area code for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the area code for the IP address
     */
    public function area_code($client_ip = NULL)
    {
        return $this->location_field('areaCode', $client_ip);
    }

    /**
     * Return the longitude for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the longitude for the IP address
     */
    public function longitude($client_ip = NULL)
    {
        return $this->location_field('longitude', $client_ip);
    }

    /**
     * Return the latitude for an IP address
     * NOTE: This method requires the MaxMiind GeoIP City database
     *
     * @param String $client_ip the client's IP address (optional)
     * @return String the latitude for the IP address
     */
    public function latitude($client_ip = NULL)
    {
        return $this->location_field('latitude', $client_ip);
    }

    /**
     * Return the client's IP address
     *
     * @return String the client's IP address
     */
    public function ip()
    {
        $forwarded_ip = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR');
        $remote_addr = array_key($_SERVER, 'REMOTE_ADDR');
        return first($forwarded_ip, $remote_addr);
    }

    /**
     * Return a location object for an IP address.
     * Note that this method requires the MaxMiind GeoIP City database.
     *
     * @param String $client_ip the client's IP address (optional)
     * @return Object the location object for the IP address
     */
    public function location($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        $location = array_key(self::$locations, $client_ip);
        if (is_null($location)) $location = $this->geoip->lookupLocation($client_ip);
        if (is_null($location)) $location = new Object();
        self::$locations[$client_ip] = $location;
        return $location;
    }

    /**
     * Return a location field for an IP address.
     * Note that this method requires the MaxMiind GeoIP City database.
     *
     * @param String $field the location field to return (e.g. "city")
     * @param String $client_ip the client's IP address (optional)
     * @return String the location field's value (e.g. "San Francisco") or NULL
     */
    private function location_field($field, $client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        try {
            $location = $this->location($client_ip);
            return $location->$field;
        } catch (Exception $e) {
            return NULL;
        }
    }
}

// End of GeoIP.php
