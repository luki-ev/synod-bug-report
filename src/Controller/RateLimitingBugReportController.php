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

use Symfony\Component\HttpFoundation\Response;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\RateLimiter\BugReportRateLimiterInterface;

class RateLimitingBugReportController
{
    private BugReportController $controller;

    private BugReportRateLimiterInterface $rateLimiter;

    public function __construct(BugReportController $controller, BugReportRateLimiterInterface $rateLimiter)
    {
        $this->controller = $controller;
        $this->rateLimiter = $rateLimiter;
    }

    public function handleRequest(BugReportRequest $request): Response
    {
        if (!$this->rateLimiter->limit($request)) {
            return new Response(Response::$statusTexts[Response::HTTP_TOO_MANY_REQUESTS], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $this->controller->handleRequest($request);
    }
}
