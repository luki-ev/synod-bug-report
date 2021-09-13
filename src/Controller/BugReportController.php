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

namespace Synod\BugReport\Controller;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;
use Synod\BugReport\BugReportFactory;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\Handler\BugReportHandlerInterface;

class BugReportController
{
    private BugReportHandlerInterface $bugReportHandler;

    private LoggerInterface $logger;

    public function __construct(BugReportHandlerInterface $bugReportHandler, ?LoggerInterface $logger = null)
    {
        $this->bugReportHandler = $bugReportHandler;
        $this->logger = $logger ?? new NullLogger();
    }

    public function handleRequest(BugReportRequest $request): Response
    {
        if ('POST' !== $request->getMethod()) {
            return new Response(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }

        try {
            $bugReport = BugReportFactory::new()->createFromFormMultiPart($request->getFormMultiPart());
            $this->bugReportHandler->handleBugReport($bugReport);

            return new Response('', Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            $this->logger->notice(sprintf('Invalid argument: %s (%s:%d)', $e->getMessage(), $e->getFile(), $e->getLine()), ['exception' => $e]);

            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }
    }
}
