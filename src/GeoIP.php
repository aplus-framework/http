<?php namespace Framework\HTTP;

/**
 * Class GeoIP
 *
 * @package Framework\HTTP
 */
class GeoIP
{
	/**
	 * @var array
	 */
	protected $record;
	/**
	 * @var string
	 */
	protected $ip;
	/**
	 * @var string
	 */
	protected $regionName;
	/**
	 * @var string
	 */
	protected $timezone;
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
	protected $isp;
	/**
	 * @var string
	 */
	protected $org;
	protected $netspeed;

	/**
	 * GeoIP constructor.
	 *
	 * @param string      $ip
	 * @param string|null $custom_directory
	 */
	public function __construct(string $ip, string $custom_directory = null)
	{
		if ($custom_directory)
		{
			\geoip_setup_custom_directory($custom_directory);
		}

		$this->parse($ip);
	}

	public function parse(string $ip)
	{
		$this->ip         = $ip;
		$this->record     = \geoip_record_by_name($ip);
		$this->regionName = null;
		$this->timezone   = null;
		$this->asn        = null;

		return $this;
	}

	public function getIP(): string
	{
		return $this->ip;
	}

	public function getContinentCode(): ?string
	{
		return $this->record['continent_code'] ?? null;
	}

	public function getCountryCode(): ?string
	{
		return $this->record['country_code'] ?? null;
	}

	public function getCountryCode3(): ?string
	{
		return $this->record['country_code3'] ?? null;
	}

	public function getCountryName(): ?string
	{
		return $this->record['country_name'] ?? null;
	}

	public function getRegion(): ?string
	{
		return $this->record['region'] ?? null;
	}

	/**
	 * Gets the region name.
	 *
	 * @return string|null
	 */
	public function getRegionName(): ?string
	{
		if ($this->regionName === null && $this->getCountryCode() && $this->getRegion())
		{
			$this->regionName = \geoip_region_name_by_code(
				$this->getCountryCode(),
				$this->getRegion()
			);
		}

		return $this->regionName ? $this->regionName : null;
	}

	public function getCity(): ?string
	{
		return $this->record['city'] ?? null;
	}

	public function getPostalCode(): ?string
	{
		return $this->record['postal_code'] ?? null;
	}

	public function getLatitude(): ?float
	{
		return $this->record['latitude'] ?? null;
	}

	public function getLongitude(): ?float
	{
		return $this->record['longitude'] ?? null;
	}

	public function getDMACode(): ?int
	{
		return $this->record['dma_code'] ?? null;
	}

	public function getAreaCode(): ?int
	{
		return $this->record['area_code'] ?? null;
	}

	/**
	 * Gets the timezone.
	 *
	 * @return string|null
	 */
	public function getTimezone(): ?string
	{
		if ($this->timezone === null && $this->getCountryCode() && $this->getRegion())
		{
			$this->timezone = \geoip_time_zone_by_country_and_region(
				$this->getCountryCode(),
				$this->getRegion()
			);
		}

		return $this->timezone ? $this->timezone : null;
	}

	public function getASN(): ?string
	{
		if ($this->asn === null && $this->record)
		{
			try
			{
				$this->asn = \geoip_asnum_by_name($this->ip);
			}
			catch (\Exception $e)
			{
				$this->asn = false;
			}
		}

		return $this->asn ? $this->asn : null;
	}

	public function getDomain(): ?string
	{
		if ($this->domain === null && $this->record)
		{
			try
			{
				$this->domain = \geoip_domain_by_name($this->ip);
			}
			catch (\Exception $e)
			{
				$this->domain = false;
			}
		}

		return $this->domain ? $this->domain : null;
	}

	public function getISP(): ?string
	{
		if ($this->isp === null && $this->record)
		{
			try
			{
				$this->isp = \geoip_isp_by_name($this->ip);
			}
			catch (\Exception $e)
			{
				$this->isp = false;
			}
		}

		return $this->isp ? $this->isp : null;
	}

	public function getORG(): ?string
	{
		if ($this->org === null && $this->record)
		{
			try
			{
				$this->org = \geoip_org_by_name($this->ip);
			}
			catch (\Exception $e)
			{
				$this->org = false;
			}
		}

		return $this->org ? $this->org : null;
	}

	public function getNetSpeed(): ?string
	{
		if ($this->netspeed === null && $this->record)
		{
			try
			{
				$this->netspeed = \geoip_netspeedcell_by_name($this->ip);
			}
			catch (\Exception $e)
			{
				$this->netspeed = false;
			}
		}

		return $this->netspeed ? $this->netspeed : null;
	}
}

