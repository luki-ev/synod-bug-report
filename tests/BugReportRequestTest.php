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

namespace Synod\BugReport\Tests;

use Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;
use Synod\BugReport\BugReportRequest;
use Synod\BugReport\Exception\InvalidRequestException;

/**
 * @covers \Synod\BugReport\BugReportRequest
 */
final class BugReportRequestTest extends TestCase
{
    public function testEmptyRequest(): void
    {
        $request = new BugReportRequest();

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Content-Type header missing');
        $request->getFormMultiPart();
    }

    public function testEmptyContent(): void
    {
        $request = new BugReportRequest();
        $request->headers->set('content-type', 'multipart/form-data; boundary=test');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Could not parse request content: Can\'t find multi-part content');
        $request->getFormMultiPart();
    }
}
