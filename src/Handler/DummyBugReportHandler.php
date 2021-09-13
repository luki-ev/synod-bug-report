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

namespace Synod\BugReport\Handler;

use Synod\BugReport\BugReport;

class DummyBugReportHandler implements BugReportHandlerInterface
{
    private ?BugReport $bugReport = null;

    public function handleBugReport(BugReport $bugReport): void
    {
        $this->bugReport = $bugReport;
    }

    public function getHandledBugReport(): ?BugReport
    {
        return $this->bugReport;
    }
}
