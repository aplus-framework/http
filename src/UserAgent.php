<?php namespace Framework\HTTP;

/**
 * Class UserAgent.
 */
class UserAgent implements \JsonSerializable
{
	/**
	 * @var string|null
	 */
	protected $agent;
	/**
	 * @var string|null
	 */
	protected $browser;
	/**
	 * @var string|null
	 */
	protected $browserVersion;
	/**
	 * @var string|null
	 */
	protected $mobile;
	/**
	 * @var string|null
	 */
	protected $platform;
	/**
	 * @var string|null
	 */
	protected $robot;
	/**
	 * @var bool
	 */
	protected $isBrowser = false;
	/**
	 * @var bool
	 */
	protected $isMobile = false;
	/**
	 * @var bool
	 */
	protected $isRobot = false;
	/**
	 * @var array
	 */
	protected static $config = [
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
			'linux' => 'Linux',
			'debian' => 'Debian',
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
			'OPR' => 'Opera',
			'Flock' => 'Flock',
			'Edge' => 'Spartan',
			'Chrome' => 'Chrome',
			// Opera 10+ always reports Opera/9.80 and appends Version/<real version> to the user agent string
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
	 * @param string $user_agent User-Agent string
	 */
	public function __construct(string $user_agent)
	{
		$this->parse($user_agent);
	}

	public function __toString()
	{
		return $this->getAgentString();
	}

	protected function parse(string $string)
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

	protected function compileData()
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
	 * Agent String.
	 *
	 * @return string
	 */
	public function getAgentString() : string
	{
		return $this->agent;
	}

	/**
	 * Gets the Browser name.
	 *
	 * @return string|null
	 */
	public function getBrowser() : ?string
	{
		return $this->browser;
	}

	/**
	 * Gets the Browser Version.
	 *
	 * @return string|null
	 */
	public function getBrowserVersion() : ?string
	{
		return $this->browserVersion;
	}

	/**
	 * Gets the Mobile device.
	 *
	 * @return string|null
	 */
	public function getMobile() : ?string
	{
		return $this->mobile;
	}

	/**
	 * Gets the OS Platform.
	 *
	 * @return string|null
	 */
	public function getPlatform() : ?string
	{
		return $this->platform;
	}

	/**
	 * Gets the Robot name.
	 *
	 * @return string|null
	 */
	public function getRobot() : ?string
	{
		return $this->robot;
	}

	/**
	 * Is Browser.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function isBrowser(string $key = null) : bool
	{
		if ($key === null || $this->isBrowser === false) {
			return $this->isBrowser;
		}
		return isset(static::$config['browsers'][$key])
			&& $this->browser === static::$config['browsers'][$key];
	}

	/**
	 * Is Mobile.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function isMobile(string $key = null) : bool
	{
		if ($key === null || $this->isMobile === false) {
			return $this->isMobile;
		}
		return isset(static::$config['mobiles'][$key])
			&& $this->mobile === static::$config['mobiles'][$key];
	}

	/**
	 * Is Robot.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function isRobot(string $key = null) : bool
	{
		if ($key === null || $this->isRobot === false) {
			return $this->isRobot;
		}
		return isset(static::$config['robots'][$key])
			&& $this->robot === static::$config['robots'][$key];
	}

	public function jsonSerialize()
	{
		return $this->getAgentString();
	}
}
