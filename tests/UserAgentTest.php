<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\UserAgent;
use PHPUnit\Framework\TestCase;

final class UserAgentTest extends TestCase
{
    protected string $mobileUserAgent = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7';
    protected string $userAgent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27';

    public function testAgentString() : void
    {
        $agent = new UserAgent($this->userAgent);
        self::assertSame($this->userAgent, $agent->toString());
        self::assertSame($this->userAgent, $agent->__toString());
    }

    public function testBot() : void
    {
        $new_agent = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
        $agent = new UserAgent($new_agent);
        self::assertFalse($agent->isBrowser());
        self::assertTrue($agent->isRobot());
        self::assertFalse($agent->isRobot('Bob'));
        self::assertFalse($agent->isMobile());
        self::assertSame('Robot', $agent->getType());
    }

    public function testBrowserInfo() : void
    {
        $agent = new UserAgent($this->userAgent);
        self::assertSame('Mac OS X', $agent->getPlatform());
        self::assertSame('Safari', $agent->getBrowser());
        self::assertSame('533.20.27', $agent->getBrowserVersion());
        self::assertNull($agent->getRobot());
        self::assertSame('Browser', $agent->getType());
        self::assertSame('Safari', $agent->getName());
    }

    public function testIsFunctions() : void
    {
        $agent = new UserAgent($this->userAgent);
        self::assertTrue($agent->isBrowser());
        self::assertTrue($agent->isBrowser('Safari'));
        self::assertFalse($agent->isBrowser('Firefox'));
        self::assertFalse($agent->isRobot());
        self::assertFalse($agent->isMobile());
    }

    public function testIsMobile() : void
    {
        $agent = new UserAgent($this->mobileUserAgent);
        self::assertTrue($agent->isMobile());
    }

    public function testMobile() : void
    {
        $new_agent = 'Mozilla/5.0 (Android; Mobile; rv:13.0) Gecko/13.0 Firefox/13.0';
        $agent = new UserAgent($new_agent);
        self::assertSame('Android', $agent->getPlatform());
        self::assertSame('Firefox', $agent->getBrowser());
        self::assertSame('13.0', $agent->getBrowserVersion());
        self::assertNull($agent->getRobot());
        self::assertSame('Android', $agent->getMobile());
        self::assertSame($new_agent, $agent->toString());
        self::assertTrue($agent->isBrowser());
        self::assertFalse($agent->isRobot());
        self::assertTrue($agent->isMobile());
        self::assertTrue($agent->isMobile('android'));
    }

    public function testUnknown() : void
    {
        $agent = new UserAgent('foo');
        self::assertSame('Unknown', $agent->getType());
        self::assertSame('Unknown', $agent->getName());
        self::assertFalse($agent->isBrowser());
        self::assertFalse($agent->isRobot());
        self::assertFalse($agent->isRobot());
        self::assertFalse($agent->isMobile());
    }

    public function testJsonSerializable() : void
    {
        self::assertSame(
            '"Mozilla\/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit\/533.20.25 (KHTML, like Gecko) Version\/5.0.4 Safari\/533.20.27"',
            \json_encode(new UserAgent(
                'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'
            ))
        );
    }
}
