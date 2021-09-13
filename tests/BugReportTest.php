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

use PHPUnit\Framework\TestCase;
use Synod\BugReport\BugReport;

/**
 * @covers \Synod\BugReport\BugReport::__construct
 */
final class BugReportTest extends TestCase
{
    /**
     * @covers \Synod\BugReport\BugReport::newBuilder
     */
    public function testNewBuilder(): void
    {
        /** @phpstan-ignore-next-line */
        static::assertNotNull(BugReport::newBuilder());
    }

    /**
     * @covers \Synod\BugReport\BugReport::getAllValues
     */
    public function testGetAllValues(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame($values, $bugReport->getAllValues());
    }

    /**
     * @covers \Synod\BugReport\BugReport::getValue
     */
    public function testGetValue(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame('value', $bugReport->getValue('value_key'));
        static::assertNull($bugReport->getValue('no_existing_key'));
        static::assertSame('default', $bugReport->getValue('no_existing_key', 'default'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::hasValue
     */
    public function testHasValue(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertTrue($bugReport->hasValue('value_key'));
        static::assertFalse($bugReport->hasValue('test'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::getAllFiles
     */
    public function testGetAllFiles(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame($files, $bugReport->getAllFiles());
    }

    /**
     * @covers \Synod\BugReport\BugReport::getFiles
     */
    public function testGetFiles(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame($files['file_key'], $bugReport->getFiles('file_key'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::hasFiles
     */
    public function testHasFiles(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertTrue($bugReport->hasFiles('file_key'));
        static::assertFalse($bugReport->hasFiles('test'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::getFile
     */
    public function testGetFile(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame('content', $bugReport->getFile('file_key', 'name'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::hasFile
     */
    public function testHasFile(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertTrue($bugReport->hasFile('file_key', 'name'));
        static::assertFalse($bugReport->hasFile('file_key', 'test'));
        static::assertFalse($bugReport->hasFile('foo', 'bar'));
    }

    /**
     * @covers \Synod\BugReport\BugReport::getLabels
     */
    public function testGetLabels(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertSame($labels, $bugReport->getLabels());
    }

    /**
     * @covers \Synod\BugReport\BugReport::hasLabel
     */
    public function testHasLabel(): void
    {
        $values = ['value_key' => 'value'];
        $files = ['file_key' => ['name' => 'content']];
        $labels = ['label'];
        $bugReport = new BugReport($values, $files, $labels);

        static::assertTrue($bugReport->hasLabel('label'));
        static::assertFalse($bugReport->hasLabel('test'));
    }
}
