<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Synod\BugReport\Tests\RateLimiter;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\RateLimiter\NikolaposaBugReportRateLimiter;

/**
 * @covers \Synod\BugReport\RateLimiter\NikolaposaBugReportRateLimiter
 */
final class NikolaposaBugReportRateLimiterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|RateLimiter
     */
    private $rateLimiterProphecy;

    protected function setUp(): void
    {
        $this->rateLimiterProphecy = $this->prophesize(RateLimiter::class);
    }

    /**
     * @covers \Synod\BugReport\RateLimiter\NikolaposaBugReportRateLimiter::limit
     */
    public function testLimitIpRatesReached(): void
    {
        $globalRates = [Rate::perDay(20), Rate::perHour(10)];
        $ipRates = [Rate::perDay(10), Rate::perHour(1)];
        $nikolaposaBugReportRateLimiter = $this->createRateLimiter($globalRates, $ipRates);
        $request = new BugReportRequest();
        $request->server->set('REMOTE_ADDR', '1.2.3.4');

        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perHour(1))->shouldBeCalledTimes(1);
        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perDay(10))->willThrow(new LimitExceeded());
        static::assertFalse($nikolaposaBugReportRateLimiter->limit($request));
    }

    /**
     * @covers \Synod\BugReport\RateLimiter\NikolaposaBugReportRateLimiter::limit
     */
    public function testLimitGlobalRatesReached(): void
    {
        $globalRates = [Rate::perDay(20), Rate::perHour(10)];
        $ipRates = [Rate::perDay(10), Rate::perHour(1)];
        $nikolaposaBugReportRateLimiter = $this->createRateLimiter($globalRates, $ipRates);
        $request = new BugReportRequest();
        $request->server->set('REMOTE_ADDR', '1.2.3.4');

        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perHour(1))->shouldBeCalledTimes(1);
        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perDay(10))->shouldBeCalledTimes(1);

        $this->rateLimiterProphecy->limit('global', Rate::perHour(10))->shouldBeCalledTimes(1);
        $this->rateLimiterProphecy->limit('global', Rate::perDay(20))->willThrow(new LimitExceeded());

        static::assertFalse($nikolaposaBugReportRateLimiter->limit($request));
    }

    /**
     * @covers \Synod\BugReport\RateLimiter\NikolaposaBugReportRateLimiter::limit
     */
    public function testLimitNotReached(): void
    {
        $globalRates = [Rate::perDay(20), Rate::perHour(10)];
        $ipRates = [Rate::perDay(10), Rate::perHour(1)];
        $nikolaposaBugReportRateLimiter = $this->createRateLimiter($globalRates, $ipRates);
        $request = new BugReportRequest();
        $request->server->set('REMOTE_ADDR', '1.2.3.4');

        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perHour(1))->shouldBeCalledTimes(1);
        $this->rateLimiterProphecy->limit('1.2.3.4', Rate::perDay(10))->shouldBeCalledTimes(1);

        $this->rateLimiterProphecy->limit('global', Rate::perHour(10))->shouldBeCalledTimes(1);
        $this->rateLimiterProphecy->limit('global', Rate::perDay(20))->shouldBeCalledTimes(1);

        static::assertTrue($nikolaposaBugReportRateLimiter->limit($request));
    }

    /**
     * @param Rate[] $globalRates
     * @param Rate[] $ipRates
     */
    private function createRateLimiter(array $globalRates, array $ipRates): NikolaposaBugReportRateLimiter
    {
        return new NikolaposaBugReportRateLimiter(
            $this->rateLimiterProphecy->reveal(),
            $globalRates,
            $ipRates
        );
    }
}
