<?php namespace Framework\HTTP;

/**
 * Class GeoIP.
 */
class GeoIP
{
	/**
	 * @var string
	 */
	protected $asn;
	/**
	 * @var string
	 */
	protected $domain;
	/**
	 * @var string
	 */
	protected $ip;
	/**
	 * @var string
	 */
	protected $isp;
	protected $netspeed;
	/**
	 * @var string
	 */
	protected $org;
	/**
	 * @var array
	 */
	protected $record;
	/**
	 * @var string
	 */
	protected $region;
	/**
	 * @var string
	 */
	protected $timezone;

	/**
	 * GeoIP constructor.
	 *
	 * @param string $ip
	 */
	public function __construct(string $ip)
	{
		$this->parse($ip);
	}

	public function getAreaCode() : ?int
	{
		return $this->record['area_code'] ?? null;
	}

	/**
	 * @return false|string|null
	 */
	public function getASN()
	{
		if ($this->asn === null && $this->record) {
			if (\geoip_db_avail(\GEOIP_ASNUM_EDITION)) {
				$this->asn = \geoip_asnum_by_name($this->ip);
			} else {
				$this->asn = false;
			}
		}
		return $this->asn;
	}

	public function getCity() : ?string
	{
		return $this->getRecord('city');
	}

	public function getContinentCode() : ?string
	{
		return $this->getRecord('continent_code');
	}

	public function getCountry() : ?string
	{
		return $this->getRecord('country_name');
	}

	public function getCountryCode() : ?string
	{
		return $this->getRecord('country_code');
	}

	public function getCountryCode3() : ?string
	{
		return $this->getRecord('country_code3');
	}

	public function getDMACode() : ?int
	{
		return $this->record['dma_code'] ?? null;
	}

	/**
	 * @return false|string|null
	 */
	public function getDomain()
	{
		if ($this->domain === null && $this->record) {
			if (\geoip_db_avail(\GEOIP_DOMAIN_EDITION)) {
				$this->domain = \geoip_domain_by_name($this->ip);
			} else {
				$this->domain = false;
			}
		}
		return $this->domain;
	}

	public function getIP() : string
	{
		return $this->ip;
	}

	/**
	 * @return false|string|null
	 */
	public function getISP()
	{
		if ($this->isp === null && $this->record) {
			if (\geoip_db_avail(\GEOIP_ISP_EDITION)) {
				$this->isp = \geoip_isp_by_name($this->ip);
			} else {
				$this->isp = false;
			}
		}
		return $this->isp;
	}

	public function getLatitude() : ?float
	{
		return $this->record['latitude'] ?? null;
	}

	public function getLongitude() : ?float
	{
		return $this->record['longitude'] ?? null;
	}

	/**
	 * @return false|string|null
	 */
	public function getNetSpeed()
	{
		if ($this->netspeed === null && $this->record) {
			if (\geoip_db_avail(\GEOIP_NETSPEED_EDITION)) {
				$this->netspeed = \geoip_netspeedcell_by_name($this->ip);
			} else {
				$this->netspeed = false;
			}
		}
		return $this->netspeed;
	}

	/**
	 * @return false|string|null
	 */
	public function getORG()
	{
		if ($this->org === null && $this->record) {
			if (\geoip_db_avail(\GEOIP_ORG_EDITION)) {
				$this->org = \geoip_org_by_name($this->ip);
			} else {
				$this->org = false;
			}
		}
		return $this->org;
	}

	public function getPostalCode() : ?string
	{
		return $this->record['postal_code'] ?? null;
	}

	protected function getRecord(string $key)
	{
		return $this->record[$key] === '' ? null : $this->record[$key];
	}

	/**
	 * Gets the region name.
	 *
	 * @return string|null
	 */
	public function getRegion() : ?string
	{
		if ($this->region === null && $this->getCountryCode() && $this->getRegionCode()) {
			$this->region = \geoip_region_name_by_code(
				$this->getCountryCode(),
				$this->getRegionCode()
			);
		}
		return $this->region ?? null;
	}

	public function getRegionCode() : ?string
	{
		return $this->getRecord('region');
	}

	/**
	 * Gets the timezone.
	 *
	 * @return string|null
	 */
	public function getTimezone() : ?string
	{
		if ($this->timezone === null && $this->getCountryCode() && $this->getRegionCode()) {
			$this->timezone = \geoip_time_zone_by_country_and_region(
				$this->getCountryCode(),
				$this->getRegionCode()
			);
		}
		return $this->timezone ?? null;
	}

	protected function parse(string $ip)
	{
		$this->ip = $ip;
		$this->record = \geoip_record_by_name($ip);
		return $this;
	}
}
