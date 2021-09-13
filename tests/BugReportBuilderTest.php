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
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Synod\BugReport\BugReportBuilder;
use Synod\BugReport\Exception\ValidationException;
use Synod\BugReport\Validate\ValidatorInterface;

/**
 * @covers \Synod\BugReport\BugReportBuilder
 */
final class BugReportBuilderTest extends TestCase
{
    use ProphecyTrait;

    private BugReportBuilder $bugReportBuilder;

    /**
     * @var ObjectProphecy|ValidatorInterface
     */
    private $validatorProphecy;

    protected function setUp(): void
    {
        $this->bugReportBuilder = new BugReportBuilder();
        $this->validatorProphecy = $this->prophesize(ValidatorInterface::class);
        BugReportBuilder::setValidator($this->validatorProphecy->reveal());
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::add
     */
    public function testAddValue(): void
    {
        $this->validatorProphecy->validateValueCount(1)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateValue('key1', 'value1')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->add('key1', 'value1');

        $this->validatorProphecy->validateValueCount(2)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateValue('key2', 'value2')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->add('key2', 'value2');
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::add
     */
    public function testAddValueTwice(): void
    {
        $this->bugReportBuilder->add('key', 'value');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only one value is allowed for key "key"');
        $this->bugReportBuilder->add('key', 'value');
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::add
     */
    public function testAddLabel(): void
    {
        $this->validatorProphecy->validateLabelCount(1)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateLabel('foo')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->add('label', 'foo');

        $this->validatorProphecy->validateLabelCount(2)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateLabel('bar')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->add('label', 'bar');
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::addFile
     */
    public function testAddFile(): void
    {
        $this->validatorProphecy->validateFileCount(1)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFile('key1', 'file1', 'content1')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->addFile('key1', 'file1', 'content1');

        $this->validatorProphecy->validateFileCount(2)->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFile('key2', 'file2', 'content2')->shouldBeCalledTimes(1);
        $this->bugReportBuilder->addFile('key2', 'file2', 'content2');
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::add
     */
    public function testAddFileTwice(): void
    {
        $this->bugReportBuilder->addFile('key', 'file', 'content');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only one file is allowed for the combination of key "key" and filename "file"');
        $this->bugReportBuilder->addFile('key', 'file', 'content');
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::build
     */
    public function testBuildWithFile(): void
    {
        $this->bugReportBuilder->addFile('key', 'file', 'content');

        $this->validatorProphecy->validateFinalFiles(['key' => ['file' => 'content']])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalLabels([])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalValues([])->shouldBeCalledTimes(1);
        $bugReport = $this->bugReportBuilder->build();

        static::assertSame(['key' => ['file' => 'content']], $bugReport->getAllFiles());
        static::assertSame([], $bugReport->getLabels());
        static::assertSame([], $bugReport->getAllValues());
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::build
     */
    public function testBuildWithLabel(): void
    {
        $this->bugReportBuilder->add('label', 'foo');

        $this->validatorProphecy->validateFinalFiles([])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalLabels(['foo'])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalValues([])->shouldBeCalledTimes(1);
        $bugReport = $this->bugReportBuilder->build();

        static::assertSame([], $bugReport->getAllFiles());
        static::assertSame(['foo'], $bugReport->getLabels());
        static::assertSame([], $bugReport->getAllValues());
    }

    /**
     * @covers \Synod\BugReport\BugReportBuilder::build
     */
    public function testBuildWithValue(): void
    {
        $this->bugReportBuilder->add('key', 'value');

        $this->validatorProphecy->validateFinalFiles([])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalLabels([])->shouldBeCalledTimes(1);
        $this->validatorProphecy->validateFinalValues(['key' => 'value'])->shouldBeCalledTimes(1);
        $bugReport = $this->bugReportBuilder->build();

        static::assertSame([], $bugReport->getAllFiles());
        static::assertSame([], $bugReport->getLabels());
        static::assertSame(['key' => 'value'], $bugReport->getAllValues());
    }
}
