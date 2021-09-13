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

namespace Synod\BugReport\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Response;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\Controller\BugReportController;
use Synod\BugReport\Controller\RateLimitingBugReportController;
use Synod\BugReport\RateLimiter\BugReportRateLimiterInterface;

/**
 * @covers \Synod\BugReport\Controller\RateLimitingBugReportController::__construct
 */
final class RateLimitingBugReportControllerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var BugReportController|ObjectProphecy
     */
    private $controllerProphecy;

    /**
     * @var BugReportRateLimiterInterface|ObjectProphecy
     */
    private $rateLimiterProphecy;

    /**
     * @var RateLimitingBugReportController
     */
    private $rateLimitingBugReportController;

    protected function setUp(): void
    {
        $this->controllerProphecy = $this->prophesize(BugReportController::class);
        $this->rateLimiterProphecy = $this->prophesize(BugReportRateLimiterInterface::class);
        $this->rateLimitingBugReportController = new RateLimitingBugReportController(
            $this->controllerProphecy->reveal(),
            $this->rateLimiterProphecy->reveal()
        );
    }

    /**
     * @covers \Synod\BugReport\Controller\RateLimitingBugReportController::handleRequest
     */
    public function testHandleRequest(): void
    {
        $request = new BugReportRequest();
        $this->rateLimiterProphecy->limit($request)->willReturn(true);
        $response = new Response('Test');
        $this->controllerProphecy->handleRequest($request)->willReturn($response);
        static::assertSame($response, $this->rateLimitingBugReportController->handleRequest($request));
    }

    /**
     * @covers \Synod\BugReport\Controller\RateLimitingBugReportController::handleRequest
     */
    public function testHandleRequestLimitReached(): void
    {
        $request = new BugReportRequest();
        $this->rateLimiterProphecy->limit($request)->willReturn(false);
        $response = $this->rateLimitingBugReportController->handleRequest($request);

        static::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        static::assertSame(Response::$statusTexts[Response::HTTP_TOO_MANY_REQUESTS], $response->getContent());
    }
}
