<?php namespace Tests\HTTP;

use Framework\HTTP\UserAgent;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
	protected $mobileUserAgent = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7';
	protected $userAgent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27';

	public function testAgentString()
	{
		$agent = new UserAgent($this->userAgent);
		$this->assertEquals($this->userAgent, $agent->getAsString());
		$this->assertEquals($this->userAgent, $agent->__toString());
	}

	public function testBot()
	{
		$new_agent = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
		$agent = new UserAgent($new_agent);
		$this->assertFalse($agent->isBrowser());
		$this->assertTrue($agent->isRobot());
		$this->assertFalse($agent->isRobot('Bob'));
		$this->assertFalse($agent->isMobile());
	}

	public function testBrowserInfo()
	{
		$agent = new UserAgent($this->userAgent);
		$this->assertEquals('Mac OS X', $agent->getPlatform());
		$this->assertEquals('Safari', $agent->getBrowser());
		$this->assertEquals('533.20.27', $agent->getBrowserVersion());
		$this->assertNull($agent->getRobot());
	}

	public function testIsFunctions()
	{
		$agent = new UserAgent($this->userAgent);
		$this->assertTrue($agent->isBrowser());
		$this->assertTrue($agent->isBrowser('Safari'));
		$this->assertFalse($agent->isBrowser('Firefox'));
		$this->assertFalse($agent->isRobot());
		$this->assertFalse($agent->isMobile());
	}

	public function testIsMobile()
	{
		$agent = new UserAgent($this->mobileUserAgent);
		$this->assertTrue($agent->isMobile());
	}

	public function testMobile()
	{
		$new_agent = 'Mozilla/5.0 (Android; Mobile; rv:13.0) Gecko/13.0 Firefox/13.0';
		$agent = new UserAgent($new_agent);
		$this->assertEquals('Android', $agent->getPlatform());
		$this->assertEquals('Firefox', $agent->getBrowser());
		$this->assertEquals('13.0', $agent->getBrowserVersion());
		$this->assertNull($agent->getRobot());
		$this->assertEquals('Android', $agent->getMobile());
		$this->assertEquals($new_agent, $agent->getAgentString());
		$this->assertEquals($new_agent, $agent->getAsString());
		$this->assertTrue($agent->isBrowser());
		$this->assertFalse($agent->isRobot());
		$this->assertTrue($agent->isMobile());
		$this->assertTrue($agent->isMobile('android'));
	}

	public function testUnknown()
	{
		$agent = new UserAgent('foo');
		$this->assertFalse($agent->isBrowser());
		$this->assertFalse($agent->isRobot());
		$this->assertFalse($agent->isRobot());
		$this->assertFalse($agent->isMobile());
	}

	public function testJsonSerializable()
	{
		$this->assertEquals(
			'"Mozilla\/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit\/533.20.25 (KHTML, like Gecko) Version\/5.0.4 Safari\/533.20.27"',
			\json_encode(new UserAgent(
				'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'
			))
		);
	}
}
