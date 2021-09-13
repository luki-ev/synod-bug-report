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

use Assert\Assertion;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\Controller\BugReportController;
use Synod\BugReport\Handler\DummyBugReportHandler;

/**
 * @covers \Synod\BugReport\Controller\BugReportController
 */
final class BugReportControllerTest extends TestCase
{
    /**
     * @var BugReportController
     */
    private $bugReportController;

    /**
     * @var DummyBugReportHandler
     */
    private $bugReportHandler;

    protected function setUp(): void
    {
        $this->bugReportHandler = new DummyBugReportHandler();
        $this->bugReportController = new BugReportController(
            $this->bugReportHandler
        );
    }

    public function testHandleRequest(): void
    {
        $boundary = '------------------------bacd997b9ded65ab';
        $content = fopen(__DIR__.'/../_data/multipart.txt', 'r');
        Assertion::isResource($content);
        $request = self::createBugReportRequest($boundary, $content);

        $response = $this->bugReportController->handleRequest($request);

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        static::assertSame('', $response->getContent());
        static::assertNotNull($this->bugReportHandler->getHandledBugReport());
    }

    public function testHandleInvalidRequest(): void
    {
        $boundary = 'test';
        $content = 'invalid';
        $request = self::createBugReportRequest($boundary, $content);

        $response = $this->bugReportController->handleRequest($request);

        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame(Response::$statusTexts[Response::HTTP_BAD_REQUEST], $response->getContent());
        static::assertNull($this->bugReportHandler->getHandledBugReport());
    }

    public function testHandleRequestGET(): void
    {
        $request = new BugReportRequest();
        $request->setMethod('GET');

        $response = $this->bugReportController->handleRequest($request);

        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        static::assertSame(Response::$statusTexts[Response::HTTP_NOT_FOUND], $response->getContent());
        static::assertNull($this->bugReportHandler->getHandledBugReport());
    }

    /**
     * @param resource|string $content
     */
    private static function createBugReportRequest(string $boundary, $content): BugReportRequest
    {
        $query = [];
        $request = [];
        $attributes = [];
        $cookies = [];
        $files = [];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary='.$boundary,
        ];

        return new BugReportRequest($query, $request, $attributes, $cookies, $files, $server, $content);
    }
}
