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

use Assert\Assertion;
use Symfony\Component\Filesystem\Filesystem;
use Synod\BugReport\BugReport;

final class FilesystemBugReportHandler implements BugReportHandlerInterface
{
    public const FLAG_NONE = 0;

    public const FLAG_PRETTY_PRINT = 1;

    private string $targetDir;

    private Filesystem $filesystem;

    private int $jsonFlags = JSON_THROW_ON_ERROR;

    public function __construct(string $targetDir, int $flags = self::FLAG_NONE, ?Filesystem $filesystem = null)
    {
        $this->targetDir = rtrim($targetDir, '/');
        if ((bool) ($flags & self::FLAG_PRETTY_PRINT)) {
            $this->jsonFlags |= JSON_PRETTY_PRINT;
        }
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function handleBugReport(BugReport $bugReport): void
    {
        $dir = $this->makeBugReportTargetDir($bugReport);
        $this->writeBugReportValues($bugReport, $dir);
        $this->writeBugReportFiles($bugReport, $dir);
        $this->writeBugReportLabels($bugReport, $dir);
    }

    private static function toSafeFilename(string $filename): string
    {
        $newFilename = preg_replace('/[^0-9a-zA-Z_]/', '_', $filename);
        Assertion::notNull($newFilename, 'preg_replace failed');

        return trim($newFilename);
    }

    private function getBugReportTargetDir(BugReport $bugReport): string
    {
        $safeDeviceId = self::toSafeFilename($bugReport->getValue('device_id') ?? '');
        Assertion::notEmpty($safeDeviceId, 'A value for "device_id" is missing.');

        return sprintf(
            '%s/%s-%s',
            $this->targetDir,
            date('YmdHis'),
            $safeDeviceId,
        );
    }

    private function makeBugReportTargetDir(BugReport $bugReport): string
    {
        $bugReportTargetDir = $this->getBugReportTargetDir($bugReport);
        Assertion::false(
            $this->filesystem->exists($bugReportTargetDir),
            sprintf('Directory for bug report "%s" already exists.', $bugReportTargetDir)
        );
        $this->filesystem->mkdir($bugReportTargetDir, 0770);

        return $bugReportTargetDir;
    }

    private function writeBugReportFiles(BugReport $bugReport, string $dir): void
    {
        foreach ($bugReport->getAllFiles() as $key => $files) {
            $filesDir = $dir.'/files/'.$key;
            $this->filesystem->mkdir($filesDir, 0770);
            foreach ($files as $name => $content) {
                $this->filesystem->dumpFile($filesDir.'/'.$name, $content);
            }
        }
    }

    private function writeBugReportLabels(BugReport $bugReport, string $dir): void
    {
        $this->filesystem->dumpFile(
            $dir.'/labels.json',
            // @phpstan-ignore-next-line (JSON_THROW_ON_ERROR is not recognized)
            json_encode($bugReport->getLabels(), $this->jsonFlags)
        );
    }

    private function writeBugReportValues(BugReport $bugReport, string $dir): void
    {
        $this->filesystem->dumpFile(
            $dir.'/values.json',
            // @phpstan-ignore-next-line (JSON_THROW_ON_ERROR is not recognized)
            json_encode($bugReport->getAllValues(), $this->jsonFlags)
        );
    }
}
