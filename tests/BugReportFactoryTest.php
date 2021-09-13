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
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Riverline\MultiPartParser\StreamedPart;
use Synod\BugReport\BugReportBuilder;
use Synod\BugReport\BugReportFactory;
use Synod\BugReport\Validate\ValidatorInterface;

/**
 * @covers \Synod\BugReport\BugReportFactory
 */
final class BugReportFactoryTest extends TestCase
{
    use ProphecyTrait;

    private BugReportFactory $bugReportFactory;

    protected function setUp(): void
    {
        // disable validation
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        BugReportBuilder::setValidator($validatorProphecy->reveal());

        $this->bugReportFactory = BugReportFactory::new();
    }

    /**
     * @covers \Synod\BugReport\BugReportFactory::createFromFormMultiPart
     */
    public function testCreateFromFormMultiPart(): void
    {
        $multipart = $this->createMultiPart(
            $this->createFilePart('filekey', 'file1', 'content1'),
            $this->createPart('label', 'foo'),
            $this->createPart('key', 'value'),
        );

        $bugReport = $this->bugReportFactory->createFromFormMultiPart($multipart);
        static::assertSame(['filekey' => ['file1' => 'content1']], $bugReport->getAllFiles());
        static::assertSame(['foo'], $bugReport->getLabels());
        static::assertSame(['key' => 'value'], $bugReport->getAllValues());
    }

    /**
     * @covers \Synod\BugReport\BugReportFactory::createFromFormMultiPart
     */
    public function testCreateFromFormMultiPartNotMultiPart(): void
    {
        $part = $this->createPart('key', 'value');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a multi-part element');
        $this->bugReportFactory->createFromFormMultiPart($part);
    }

    /**
     * @covers \Synod\BugReport\BugReportFactory::createFromFormMultiPart
     */
    public function testCreateFromFormMultiPartNestedMultiPart(): void
    {
        $part = $this->createMultiPart($this->createMultiPart());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected multi-part element with name ""');
        $this->bugReportFactory->createFromFormMultiPart($part);
    }

    /**
     * @covers \Synod\BugReport\BugReportFactory::createFromFormMultiPart
     */
    public function testCreateFromFormMultiPartEmptyName(): void
    {
        $multipart = $this->createMultiPart(
            $this->createPart('', 'foo'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header option name in part element is missing');
        $this->bugReportFactory->createFromFormMultiPart($multipart);
    }

    /**
     * @covers \Synod\BugReport\BugReportFactory::createFromFormMultiPart
     */
    public function testCreateFromFormMultiPartEmptyFilename(): void
    {
        $multipart = $this->createMultiPart(
            $this->createFilePart('key', '', 'content'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header option filename in part element with name "key" is missing');
        $this->bugReportFactory->createFromFormMultiPart($multipart);
    }

    private function createMultiPart(StreamedPart ...$parts): StreamedPart
    {
        /** @var ObjectProphecy|StreamedPart $streamedPartProphecy */
        $streamedPartProphecy = $this->prophesize(StreamedPart::class);
        $streamedPartProphecy->isMultiPart()->willReturn(true);
        $streamedPartProphecy->isFile()->willReturn(false);
        $streamedPartProphecy->getName()->willReturn(null);
        $streamedPartProphecy->getBody()->willThrow(new \LogicException("MultiPart content, there aren't body"));
        $streamedPartProphecy->getParts()->willReturn($parts);

        $streamedPartProphecy->getPartsByName(Argument::type('string'))->will(function ($args): array {
            $name = $args[0];
            $parts = [];
            /** @var StreamedPart $self */
            $self = $this;
            foreach ($self->getParts() as $part) {
                if ($part->getName() === $name) {
                    $parts[] = $part;
                }
            }

            return $parts;
        });

        return $streamedPartProphecy->reveal();
    }

    private function createPart(string $name, string $body): StreamedPart
    {
        /** @var ObjectProphecy|StreamedPart $streamedPartProphecy */
        $streamedPartProphecy = $this->prophesize(StreamedPart::class);
        $streamedPartProphecy->isMultiPart()->willReturn(false);
        $streamedPartProphecy->isFile()->willReturn(false);
        $streamedPartProphecy->getName()->willReturn($name);
        $streamedPartProphecy->getBody()->willReturn($body);
        $streamedPartProphecy->getParts()->willThrow(new \LogicException("Not MultiPart content, there aren't any parts"));
        $streamedPartProphecy->getPartsByName(Argument::type('string'))->willThrow(new \LogicException("Not MultiPart content, there aren't any parts"));

        return $streamedPartProphecy->reveal();
    }

    private function createFilePart(string $name, string $filename, string $body): StreamedPart
    {
        /** @var ObjectProphecy|StreamedPart $streamedPartProphecy */
        $streamedPartProphecy = $this->prophesize(StreamedPart::class);
        $streamedPartProphecy->isMultiPart()->willReturn(false);
        $streamedPartProphecy->isFile()->willReturn(true);
        $streamedPartProphecy->getName()->willReturn($name);
        $streamedPartProphecy->getFileName()->willReturn($filename);
        $streamedPartProphecy->getBody()->willReturn($body);
        $streamedPartProphecy->getParts()->willThrow(new \LogicException("Not MultiPart content, there aren't any parts"));
        $streamedPartProphecy->getPartsByName(Argument::type('string'))->willThrow(new \LogicException("Not MultiPart content, there aren't any parts"));

        return $streamedPartProphecy->reveal();
    }
}
