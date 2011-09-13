<?
load_plugin('Net/Browscap');

class UserAgent
{
    const BROWSCAP_CACHE_DIR = 'app/tmp/browscap';
    const MAX_USER_AGENTS_COUNT = 1000;
    private static $browscap = NULL;
    private static $user_agents = array();

    public static function details($user_agent)
    {
        if (is_null(self::$browscap))
        {
            $cache_dir = self::BROWSCAP_CACHE_DIR;
            if (!is_dir($cache_dir)) mkdir($cache_dir);
            self::$browscap = new Browscap($cache_dir);
        }

        // Reset the user agents cache if we've cached too many

        if (count(self::$user_agents) > self::MAX_USER_AGENTS_COUNT)
        {
            self::$user_agents = array();
        }

        // If user agent info is cached then return it

        if ($details = array_key(self::$user_agents, $user_agent))
        {
            return $details;
        }

        // Look up the user agent using the browscap.ini file

        $browscap = self::$browscap->getBrowser($user_agent, TRUE);
        $browser = array_key($browscap, 'Browser'); // e.g. "IE"
        $version = array_key($browscap, 'Parent'); // e.g. "IE 9.0"
        $version = ($version && $browser && strpos($version, $browser) === 0) ?
                                substr($version, strlen($browser)+1) : $version;
        $op_sys = array_key($browscap, 'Platform');
        $is_mobile = array_key($browscap, 'isMobileDevice');
        $details = array(
            'op_sys' => $op_sys,
            'browser' => $browser,
            'version' => $version,
            'browser_version' => $browser . ($version ? " $version" : ''),
            'is_robot' => ($op_sys == 'unknown' ? TRUE : FALSE),
            'is_mobile' => $is_mobile,
        );
        return self::$user_agents[$user_agent] = new Object($details);
    }
}

// End of UserAgent.php
