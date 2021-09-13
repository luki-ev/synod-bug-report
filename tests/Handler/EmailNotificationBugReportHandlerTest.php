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
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Synod\BugReport\BugReport;
use Synod\BugReport\Handler\EmailNotificationBugReportHandler;

/**
 * @covers \Synod\BugReport\Handler\EmailNotificationBugReportHandler
 */
final class EmailNotificationBugReportHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var EmailNotificationBugReportHandler
     */
    private $emailNotificationBugReportHandler;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $loggerProphecy;

    /**
     * @var MailerInterface|ObjectProphecy
     */
    private $mailerProphecy;

    public static function setUpBeforeClass(): void
    {
        ClockMock::register(__CLASS__);
        ClockMock::withClockMock(strtotime('2000-01-02 03:04:05'));
    }

    protected function setUp(): void
    {
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);
        $this->mailerProphecy = $this->prophesize(MailerInterface::class);

        $this->emailNotificationBugReportHandler = new EmailNotificationBugReportHandler(
            $this->mailerProphecy->reveal(),
            'from@example.org',
            ['to@example.org'],
            $this->loggerProphecy->reveal()
        );
    }

    /**
     * @covers \Synod\BugReport\Handler\EmailNotificationBugReportHandler::handleBugReport
     */
    public function testSendEmail(): void
    {
        $bugReport = new BugReport([
            'user_id' => '@test:example.org',
            'device_id' => 'ABC',
            'user_agent' => 'Test',
            'text' => 'Text',
            'version' => '1.0',
            'build' => '#21',
        ], [], []);

        $expectedText = <<<'EOD'
            A new bug report has been submitted at 2000-01-02 03:04:05

            user_id: @test:example.org
            device_id: ABC
            user_agent: Test
            version: 1.0
            build: #21
            text: Text

            EOD;
        $expectedEmail = (new Email())
            ->from('from@example.org')
            ->to('to@example.org')
            ->subject('New bug report')
            ->text($expectedText)
        ;
        $this->mailerProphecy->send($expectedEmail)->shouldBeCalledTimes(1);

        $this->emailNotificationBugReportHandler->handleBugReport($bugReport);
    }

    /**
     * @covers \Synod\BugReport\Handler\EmailNotificationBugReportHandler::setValuesToSend
     */
    public function testSendEmailWithCustomValuesToSend(): void
    {
        $this->emailNotificationBugReportHandler->setValuesToSend(['foo']);
        $bugReport = new BugReport(['foo' => 'bar'], [], []);

        $expectedText = <<<'EOD'
            A new bug report has been submitted at 2000-01-02 03:04:05

            foo: bar

            EOD;
        $expectedEmail = (new Email())
            ->from('from@example.org')
            ->to('to@example.org')
            ->subject('New bug report')
            ->text($expectedText)
        ;
        $this->mailerProphecy->send($expectedEmail)->shouldBeCalledTimes(1);

        $this->emailNotificationBugReportHandler->handleBugReport($bugReport);
    }

    /**
     * @covers \Synod\BugReport\Handler\EmailNotificationBugReportHandler::handleBugReport
     */
    public function testSendEmailFailure(): void
    {
        $bugReport = new BugReport([], [], []);

        $transportException = new TransportException('test');
        $this->mailerProphecy->send(\Prophecy\Argument::type(Email::class))->willThrow($transportException);

        $this->loggerProphecy->error('Sending email failed: test', ['exception' => $transportException])->shouldBeCalledTimes(1);
        $this->emailNotificationBugReportHandler->handleBugReport($bugReport);
    }
}
