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

namespace Synod\BugReport\Tests\Handler;

use PHPUnit\Framework\TestCase;
use Synod\BugReport\BugReport;
use Synod\BugReport\Handler\DummyBugReportHandler;

/**
 * @covers \Synod\BugReport\Handler\DummyBugReportHandler
 */
final class DummyBugReportHandlerTest extends TestCase
{
    /**
     * @var DummyBugReportHandler
     */
    private $dummyBugReportHandler;

    protected function setUp(): void
    {
        $this->dummyBugReportHandler = new DummyBugReportHandler();
    }

    /**
     * @covers \Synod\BugReport\Handler\DummyBugReportHandler::getHandledBugReport
     * @covers \Synod\BugReport\Handler\DummyBugReportHandler::handleBugReport
     */
    public function testHandleBugReport(): void
    {
        $bugReport = new BugReport([], [], []);
        $this->dummyBugReportHandler->handleBugReport($bugReport);
        static::assertSame($bugReport, $this->dummyBugReportHandler->getHandledBugReport());
    }
}
