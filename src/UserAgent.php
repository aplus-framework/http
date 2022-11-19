<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

/**
 * Class UserAgent.
 *
 * @package http
 */
class UserAgent implements \JsonSerializable, \Stringable
{
    protected ?string $agent = null;
    protected ?string $browser = null;
    protected ?string $browserVersion = null;
    protected ?string $mobile = null;
    protected ?string $platform = null;
    protected ?string $robot = null;
    protected bool $isBrowser = false;
    protected bool $isMobile = false;
    protected bool $isRobot = false;
    /**
     * @var array<string,array<string,string>>
     */
    protected static array $config = [
        'platforms' => [
            'windows nt 10.0' => 'Windows 10',
            'windows nt 6.3' => 'Windows 8.1',
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows 2003',
            'windows nt 5.1' => 'Windows XP',
            'windows nt 5.0' => 'Windows 2000',
            'windows nt 4.0' => 'Windows NT 4.0',
            'winnt4.0' => 'Windows NT 4.0',
            'winnt 4.0' => 'Windows NT',
            'winnt' => 'Windows NT',
            'windows 98' => 'Windows 98',
            'win98' => 'Windows 98',
            'windows 95' => 'Windows 95',
            'win95' => 'Windows 95',
            'windows phone' => 'Windows Phone',
            'windows' => 'Unknown Windows OS',
            'android' => 'Android',
            'blackberry' => 'BlackBerry',
            'iphone' => 'iOS',
            'ipad' => 'iOS',
            'ipod' => 'iOS',
            'os x' => 'Mac OS X',
            'ppc mac' => 'Power PC Mac',
            'freebsd' => 'FreeBSD',
            'ppc' => 'Macintosh',
            'ubuntu' => 'Ubuntu',
            'debian' => 'Debian',
            'fedora' => 'Fedora',
            'linux' => 'Linux',
            'sunos' => 'Sun Solaris',
            'beos' => 'BeOS',
            'apachebench' => 'ApacheBench',
            'aix' => 'AIX',
            'irix' => 'Irix',
            'osf' => 'DEC OSF',
            'hp-ux' => 'HP-UX',
            'netbsd' => 'NetBSD',
            'bsdi' => 'BSDi',
            'openbsd' => 'OpenBSD',
            'gnu' => 'GNU/Linux',
            'unix' => 'Unknown Unix OS',
            'symbian' => 'Symbian OS',
        ],
        // The order of this array should NOT be changed. Many browsers return
        // multiple browser types so we want to identify the sub-type first.
        'browsers' => [
            'curl' => 'Curl',
            'PostmanRuntime' => 'Postman',
            'OPR' => 'Opera',
            'Flock' => 'Flock',
            'Edge' => 'Spartan',
            'Edg' => 'Edge',
            'EdgA' => 'Edge',
            'Chrome' => 'Chrome',
            // Opera 10+ always reports Opera/9.80 and appends
            // Version/<real version> to the user agent string
            'Opera.*?Version' => 'Opera',
            'Opera' => 'Opera',
            'MSIE' => 'Internet Explorer',
            'Internet Explorer' => 'Internet Explorer',
            'Trident.* rv' => 'Internet Explorer',
            'Shiira' => 'Shiira',
            'Firefox' => 'Firefox',
            'Chimera' => 'Chimera',
            'Phoenix' => 'Phoenix',
            'Firebird' => 'Firebird',
            'Camino' => 'Camino',
            'Netscape' => 'Netscape',
            'OmniWeb' => 'OmniWeb',
            'Safari' => 'Safari',
            'Mozilla' => 'Mozilla',
            'Konqueror' => 'Konqueror',
            'icab' => 'iCab',
            'Lynx' => 'Lynx',
            'Links' => 'Links',
            'hotjava' => 'HotJava',
            'amaya' => 'Amaya',
            'IBrowse' => 'IBrowse',
            'Maxthon' => 'Maxthon',
            'Ubuntu' => 'Ubuntu Web Browser',
            'Vivaldi' => 'Vivaldi',
        ],
        'mobiles' => [
            'mobileexplorer' => 'Mobile Explorer',
            'palmsource' => 'Palm',
            'palmscape' => 'Palmscape',
            // Phones and Manufacturers
            'motorola' => 'Motorola',
            'nokia' => 'Nokia',
            'palm' => 'Palm',
            'iphone' => 'Apple iPhone',
            'ipad' => 'iPad',
            'ipod' => 'Apple iPod Touch',
            'sony' => 'Sony Ericsson',
            'ericsson' => 'Sony Ericsson',
            'blackberry' => 'BlackBerry',
            'cocoon' => 'O2 Cocoon',
            'blazer' => 'Treo',
            'lg' => 'LG',
            'amoi' => 'Amoi',
            'xda' => 'XDA',
            'mda' => 'MDA',
            'vario' => 'Vario',
            'htc' => 'HTC',
            'samsung' => 'Samsung',
            'sharp' => 'Sharp',
            'sie-' => 'Siemens',
            'alcatel' => 'Alcatel',
            'benq' => 'BenQ',
            'ipaq' => 'HP iPaq',
            'mot-' => 'Motorola',
            'playstation portable' => 'PlayStation Portable',
            'playstation 3' => 'PlayStation 3',
            'playstation vita' => 'PlayStation Vita',
            'hiptop' => 'Danger Hiptop',
            'nec-' => 'NEC',
            'panasonic' => 'Panasonic',
            'philips' => 'Philips',
            'sagem' => 'Sagem',
            'sanyo' => 'Sanyo',
            'spv' => 'SPV',
            'zte' => 'ZTE',
            'sendo' => 'Sendo',
            'nintendo dsi' => 'Nintendo DSi',
            'nintendo ds' => 'Nintendo DS',
            'nintendo 3ds' => 'Nintendo 3DS',
            'wii' => 'Nintendo Wii',
            'open web' => 'Open Web',
            'openweb' => 'OpenWeb',
            // Operating Systems
            'android' => 'Android',
            'symbian' => 'Symbian',
            'SymbianOS' => 'SymbianOS',
            'elaine' => 'Palm',
            'series60' => 'Symbian S60',
            'windows ce' => 'Windows CE',
            // Browsers
            'obigo' => 'Obigo',
            'netfront' => 'Netfront Browser',
            'openwave' => 'Openwave Browser',
            'mobilexplorer' => 'Mobile Explorer',
            'operamini' => 'Opera Mini',
            'opera mini' => 'Opera Mini',
            'opera mobi' => 'Opera Mobile',
            'fennec' => 'Firefox Mobile',
            // Other
            'digital paths' => 'Digital Paths',
            'avantgo' => 'AvantGo',
            'xiino' => 'Xiino',
            'novarra' => 'Novarra Transcoder',
            'vodafone' => 'Vodafone',
            'docomo' => 'NTT DoCoMo',
            'o2' => 'O2',
            // Fallback
            'mobile' => 'Generic Mobile',
            'wireless' => 'Generic Mobile',
            'j2me' => 'Generic Mobile',
            'midp' => 'Generic Mobile',
            'cldc' => 'Generic Mobile',
            'up.link' => 'Generic Mobile',
            'up.browser' => 'Generic Mobile',
            'smartphone' => 'Generic Mobile',
            'cellphone' => 'Generic Mobile',
        ],
        'robots' => [
            'googlebot' => 'Googlebot',
            'msnbot' => 'MSNBot',
            'baiduspider' => 'Baiduspider',
            'bingbot' => 'Bing',
            'slurp' => 'Inktomi Slurp',
            'yahoo' => 'Yahoo',
            'ask jeeves' => 'Ask Jeeves',
            'fastcrawler' => 'FastCrawler',
            'infoseek' => 'InfoSeek Robot 1.0',
            'lycos' => 'Lycos',
            'yandex' => 'YandexBot',
            'mediapartners-google' => 'MediaPartners Google',
            'CRAZYWEBCRAWLER' => 'Crazy Webcrawler',
            'adsbot-google' => 'AdsBot Google',
            'feedfetcher-google' => 'Feedfetcher Google',
            'curious george' => 'Curious George',
            'ia_archiver' => 'Alexa Crawler',
            'MJ12bot' => 'Majestic-12',
            'Uptimebot' => 'Uptimebot',
        ],
    ];

    /**
     * UserAgent constructor.
     *
     * @param string $userAgent User-Agent string
     */
    public function __construct(string $userAgent)
    {
        $this->parse($userAgent);
    }

    #[Pure]
    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * @param string $string
     *
     * @return static
     */
    protected function parse(string $string) : static
    {
        $this->isBrowser = false;
        $this->isRobot = false;
        $this->isMobile = false;
        $this->browser = null;
        $this->browserVersion = null;
        $this->mobile = null;
        $this->robot = null;
        $this->agent = $string;
        $this->compileData();
        return $this;
    }

    protected function compileData() : void
    {
        $this->setPlatform();
        foreach (['setRobot', 'setBrowser', 'setMobile'] as $function) {
            if ($this->{$function}()) {
                break;
            }
        }
    }

    protected function setPlatform() : bool
    {
        foreach (static::$config['platforms'] as $key => $val) {
            if (\preg_match('#' . \preg_quote($key, '#') . '#i', $this->agent)) {
                $this->platform = $val;
                return true;
            }
        }
        return false;
    }

    protected function setBrowser() : bool
    {
        foreach (static::$config['browsers'] as $key => $val) {
            if (\preg_match(
                '#' . \preg_quote($key, '#') . '.*?([0-9\.]+)#i',
                $this->agent,
                $match
            )) {
                $this->isBrowser = true;
                $this->browserVersion = $match[1];
                $this->browser = $val;
                $this->setMobile();
                return true;
            }
        }
        return false;
    }

    protected function setMobile() : bool
    {
        foreach (static::$config['mobiles'] as $key => $val) {
            if (\stripos($this->agent, $key) !== false) {
                $this->isMobile = true;
                $this->mobile = $val;
                return true;
            }
        }
        return false;
    }

    protected function setRobot() : bool
    {
        foreach (static::$config['robots'] as $key => $val) {
            if (\preg_match('#' . \preg_quote($key, '#') . '#i', $this->agent)) {
                $this->isRobot = true;
                $this->robot = $val;
                $this->setMobile();
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     *
     * @deprecated Use {@see UserAgent::toString()}
     *
     * @codeCoverageIgnore
     */
    #[Deprecated(
        reason: 'since HTTP Library version 5.3, use toString() instead',
        replacement: '%class%->toString()'
    )]
    public function getAsString() : string
    {
        \trigger_error(
            'Method ' . __METHOD__ . ' is deprecated',
            \E_USER_DEPRECATED
        );
        return $this->toString();
    }

    /**
     * Get the User-Agent as string.
     *
     * @since 5.3
     *
     * @return string
     */
    #[Pure]
    public function toString() : string
    {
        return $this->agent;
    }

    /**
     * Gets the Browser name.
     *
     * @return string|null
     */
    #[Pure]
    public function getBrowser() : ?string
    {
        return $this->browser;
    }

    /**
     * Gets the Browser Version.
     *
     * @return string|null
     */
    #[Pure]
    public function getBrowserVersion() : ?string
    {
        return $this->browserVersion;
    }

    /**
     * Gets the Mobile device.
     *
     * @return string|null
     */
    #[Pure]
    public function getMobile() : ?string
    {
        return $this->mobile;
    }

    /**
     * Gets the OS Platform.
     *
     * @return string|null
     */
    #[Pure]
    public function getPlatform() : ?string
    {
        return $this->platform;
    }

    /**
     * Gets the Robot name.
     *
     * @return string|null
     */
    #[Pure]
    public function getRobot() : ?string
    {
        return $this->robot;
    }

    /**
     * Is Browser.
     *
     * @param string|null $key
     *
     * @return bool
     */
    #[Pure]
    public function isBrowser(string $key = null) : bool
    {
        if ($key === null || $this->isBrowser === false) {
            return $this->isBrowser;
        }
        $config = static::$config['browsers'] ?? [];
        return isset($config[$key])
            && $this->browser === $config[$key];
    }

    /**
     * Is Mobile.
     *
     * @param string|null $key
     *
     * @return bool
     */
    #[Pure]
    public function isMobile(string $key = null) : bool
    {
        if ($key === null || $this->isMobile === false) {
            return $this->isMobile;
        }
        $config = static::$config['mobiles'] ?? [];
        return isset($config[$key])
            && $this->mobile === $config[$key];
    }

    /**
     * Is Robot.
     *
     * @param string|null $key
     *
     * @return bool
     */
    #[Pure]
    public function isRobot(string $key = null) : bool
    {
        if ($key === null || $this->isRobot === false) {
            return $this->isRobot;
        }
        $config = static::$config['robots'] ?? [];
        return isset($config[$key])
            && $this->robot === $config[$key];
    }

    #[Pure]
    public function getType() : string
    {
        if ($this->isBrowser()) {
            return 'Browser';
        }
        if ($this->isRobot()) {
            return 'Robot';
        }
        return 'Unknown';
    }

    #[Pure]
    public function getName() : string
    {
        if ($this->isBrowser()) {
            return $this->getBrowser();
        }
        if ($this->isRobot()) {
            return $this->getRobot();
        }
        return 'Unknown';
    }

    #[Pure]
    public function jsonSerialize() : string
    {
        return $this->toString();
    }
}
