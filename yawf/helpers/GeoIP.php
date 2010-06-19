<?
class GeoIP
{
    private static $geoip;

    public function __construct()
    {
        return self::get_instance();
    }

    public function country_name($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        return self::$geoip->lookupCountryName($client_ip);
    }

    public function country_code($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        return self::$geoip->lookupCountryCode($client_ip);
    }

    public function location($client_ip = NULL)
    {
        if (is_null($client_ip)) $client_ip = $this->ip();
        $location = self::$geoip->lookupLocation($client_ip);
        if (is_null($location)) $location = new Object();
        return $location;
    }

    public function ip()
    {
        $forwarded_ip = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR');
        $remote_addr = array_key($_SERVER, 'REMOTE_ADDR');
        return first($forwarded_ip, $remote_addr);
    }

    private static function get_instance()
    {
        if (self::$geoip) return self::$geoip;
        load_plugin('Net/GeoIP');
        self::$geoip = Net_GeoIP::getInstance('app/data/GeoIPCity.dat');
        return self::$geoip;
    }
}

// End of GeoIP.php
