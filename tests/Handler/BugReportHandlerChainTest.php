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
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Synod\BugReport\BugReport;
use Synod\BugReport\Handler\BugReportHandlerChain;
use Synod\BugReport\Handler\BugReportHandlerInterface;

/**
 * @covers \Synod\BugReport\Handler\BugReportHandlerChain::__construct
 */
final class BugReportHandlerChainTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var BugReportHandlerChain
     */
    private $bugReportHandlerChain;

    /**
     * @var BugReportHandlerInterface|ObjectProphecy
     */
    private $bugReportHandler1Prophecy;

    /**
     * @var BugReportHandlerInterface|ObjectProphecy
     */
    private $bugReportHandler2Prophecy;

    protected function setUp(): void
    {
        $this->bugReportHandler1Prophecy = $this->prophesize(BugReportHandlerInterface::class);
        $this->bugReportHandler2Prophecy = $this->prophesize(BugReportHandlerInterface::class);
        $this->bugReportHandlerChain = new BugReportHandlerChain(
            $this->bugReportHandler1Prophecy->reveal(),
            $this->bugReportHandler2Prophecy->reveal(),
        );
    }

    /**
     * @covers \Synod\BugReport\Handler\BugReportHandlerChain::handleBugReport
     */
    public function testHandleBugReport(): void
    {
        $bugReport = new BugReport([], [], []);
        $this->bugReportHandler1Prophecy->handleBugReport($bugReport)->shouldBeCalledTimes(1);
        $this->bugReportHandler2Prophecy->handleBugReport($bugReport)->shouldBeCalledTimes(1);

        $this->bugReportHandlerChain->handleBugReport($bugReport);
    }
}
