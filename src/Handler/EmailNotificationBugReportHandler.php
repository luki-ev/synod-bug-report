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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Synod\BugReport\BugReport;

final class EmailNotificationBugReportHandler implements BugReportHandlerInterface
{
    private string|Address $fromAddress;

    private LoggerInterface $logger;

    private MailerInterface $mailer;

    /**
     * @var array<Address|string>
     */
    private array $toAddresses;

    /**
     * @var string[]
     */
    private array $valuesToSend = [
        'user_id',
        'device_id',
        'user_agent',
        'version',
        'build',
        'text',
    ];

    /**
     * @param Address|string $fromAddress
     * @param array<Address|string> $toAddresses
     */
    public function __construct(MailerInterface $mailer, string|Address $fromAddress, array $toAddresses, ?LoggerInterface $logger = null)
    {
        $this->mailer = $mailer;
        $this->fromAddress = $fromAddress;
        $this->toAddresses = $toAddresses;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string[] $valuesToSend
     */
    public function setValuesToSend(array $valuesToSend): void
    {
        $this->valuesToSend = $valuesToSend;
    }

    public function handleBugReport(BugReport $bugReport): void
    {
        $email = (new Email())
            ->from($this->fromAddress)
            ->to(...$this->toAddresses)
            ->subject('New bug report')
            ->text($this->buildText($bugReport))
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('Sending email failed: %s', $e->getMessage()), ['exception' => $e]);
        }
    }

    private function buildText(BugReport $bugReport): string
    {
        $text = sprintf("A new bug report has been submitted at %s\n\n", date('Y-m-d H:i:s'));
        foreach ($this->valuesToSend as $key) {
            $text .= sprintf("%s: %s\n", $key, $bugReport->getValue($key));
        }

        return $text;
    }
}
