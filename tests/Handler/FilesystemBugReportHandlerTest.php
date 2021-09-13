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

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Filesystem\Filesystem;
use Synod\BugReport\BugReport;
use Synod\BugReport\Handler\FilesystemBugReportHandler;

/**
 * @covers \Synod\BugReport\Handler\FilesystemBugReportHandler
 */
final class FilesystemBugReportHandlerTest extends TestCase
{
    use ProphecyTrait;

    private FilesystemBugReportHandler $filesystemBugReportHandler;

    /**
     * @var Filesystem|ObjectProphecy
     */
    private $filesystemProphecy;

    private string $targetDir = '/test';

    public static function setUpBeforeClass(): void
    {
        ClockMock::register(__CLASS__);
        ClockMock::withClockMock(strtotime('2000-11-22 03:04:05'));
    }

    protected function setUp(): void
    {
        $this->filesystemProphecy = $this->prophesize(Filesystem::class);

        $this->filesystemBugReportHandler = new FilesystemBugReportHandler(
            $this->targetDir,
            FilesystemBugReportHandler::FLAG_PRETTY_PRINT,
            $this->filesystemProphecy->reveal(),
        );
    }

    public function testHandleBugReport(): void
    {
        $values = [
            'device_id' => 'foo/bar',
            'key' => 'value',
        ];
        $files = [
            'key1' => [
                'file1' => 'content1',
                'file2' => 'content2',
            ],
            'key2' => [
                'file1' => 'content3',
            ],
        ];
        $labels = [
            'bar',
            'baz',
        ];
        $bugReport = new BugReport($values, $files, $labels);

        $this->filesystemProphecy->exists('/test/20001122030405-foo_bar')->willReturn(false)->shouldBeCalledTimes(1);
        $this->filesystemProphecy->mkdir('/test/20001122030405-foo_bar', 0770)->shouldBeCalledTimes(1);

        $this->filesystemProphecy->dumpFile('/test/20001122030405-foo_bar/values.json', json_encode($values, JSON_PRETTY_PRINT))
            ->shouldBeCalledTimes(1)
        ;

        $this->filesystemProphecy->dumpFile('/test/20001122030405-foo_bar/labels.json', json_encode($labels, JSON_PRETTY_PRINT))
            ->shouldBeCalledTimes(1)
        ;

        $this->filesystemProphecy->mkdir('/test/20001122030405-foo_bar/files/key1', 0770)->shouldBeCalledTimes(1);
        $this->filesystemProphecy->dumpFile('/test/20001122030405-foo_bar/files/key1/file1', 'content1')->shouldBeCalledTimes(1);
        $this->filesystemProphecy->dumpFile('/test/20001122030405-foo_bar/files/key1/file2', 'content2')->shouldBeCalledTimes(1);

        $this->filesystemProphecy->mkdir('/test/20001122030405-foo_bar/files/key2', 0770)->shouldBeCalledTimes(1);
        $this->filesystemProphecy->dumpFile('/test/20001122030405-foo_bar/files/key2/file1', 'content3')->shouldBeCalledTimes(1);

        $this->filesystemBugReportHandler->handleBugReport($bugReport);
    }

    public function testHandleBugReportAssertsEmptyDeviceId(): void
    {
        $values = ['device_id' => ''];
        $files = [];
        $labels = [];
        $bugReport = new BugReport($values, $files, $labels);

        $this->expectException(InvalidArgumentException::class);
        $this->filesystemBugReportHandler->handleBugReport($bugReport);
    }
}
