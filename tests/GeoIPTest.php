<?php namespace Tests\HTTP;

use Framework\HTTP\GeoIP;
use PHPUnit\Framework\TestCase;

class GeoIPTest extends TestCase
{
	/**
	 * @var GeoIP
	 */
	protected static $geoip;
	/**
	 * @var string
	 */
	protected $ip = '108.177.122.136';

	public function getGeoIP()
	{
		if (empty(static::$geoip)) {
			static::$geoip = new GeoIP($this->ip);
		}
		return static::$geoip;
	}

	public function testAreaCode()
	{
		$this->assertEquals(650, $this->getGeoIP()->getAreaCode());
	}

	public function testASN()
	{
		$this->assertEquals('AS15169 Google LLC', $this->getGeoIP()->getASN());
	}

	public function testCity()
	{
		$this->assertEquals('Mountain View', $this->getGeoIP()->getCity());
	}

	public function testContinentCode()
	{
		$this->assertEquals('NA', $this->getGeoIP()->getContinentCode());
	}

	public function testCountry()
	{
		$this->assertEquals('United States', $this->getGeoIP()->getCountry());
	}

	public function testCountryCode()
	{
		$this->assertEquals('US', $this->getGeoIP()->getCountryCode());
	}

	public function testCountryCode3()
	{
		$this->assertEquals('USA', $this->getGeoIP()->getCountryCode3());
	}

	public function testDMACode()
	{
		$this->assertEquals(807, $this->getGeoIP()->getDMACode());
	}

	public function testDomain()
	{
		$this->assertFalse($this->getGeoIP()->getDomain());
	}

	public function testIP()
	{
		$this->assertEquals($this->ip, $this->getGeoIP()->getIP());
	}

	public function testISP()
	{
		$this->assertFalse($this->getGeoIP()->getISP());
	}

	public function testLatitude()
	{
		$this->assertEquals(37.419200897217, $this->getGeoIP()->getLatitude());
	}

	public function testLongitude()
	{
		$this->assertEquals(-122.05740356445, $this->getGeoIP()->getLongitude());
	}

	public function testNetSpeed()
	{
		$this->assertFalse($this->getGeoIP()->getNetSpeed());
	}

	public function testORG()
	{
		$this->assertFalse($this->getGeoIP()->getORG());
	}

	public function testPostalCode()
	{
		$this->assertEquals('94043', $this->getGeoIP()->getPostalCode());
	}

	public function testRegion()
	{
		$this->assertEquals('California', $this->getGeoIP()->getRegion());
	}

	public function testRegionCode()
	{
		$this->assertEquals('CA', $this->getGeoIP()->getRegionCode());
	}

	public function testTimezone()
	{
		$this->assertEquals('America/Los_Angeles', $this->getGeoIP()->getTimezone());
	}

	public function testJsonSerializable()
	{
		$this->assertEquals(
			<<<JSON
{
    "continentCode": "NA",
    "timezone": "America\\/Los_Angeles",
    "country": "United States",
    "countryCode": "US",
    "countryCode3": "USA",
    "region": "California",
    "regionCode": "CA",
    "postalCode": "94043",
    "areaCode": 650,
    "ASN": "AS15169 Google LLC",
    "DMACode": 807,
    "IP": "108.177.122.136",
    "ISP": false,
    "domain": false,
    "netSpeed": false,
    "latitude": 37.4192008972168,
    "longitude": -122.05740356445312,
    "ORG": false
}
JSON,
			\json_encode($this->getGeoIP(), \JSON_PRETTY_PRINT)
		);
	}
}
