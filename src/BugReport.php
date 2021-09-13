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

namespace Synod\BugReport;

class BugReport
{
    /**
     * @var array<string, string>
     */
    private array $values;

    /**
     * @var array<string, array<string, string>>
     */
    private array $files;

    /**
     * @var string[]
     */
    private array $labels;

    /**
     * @param array<string, string> $values
     * @param array<string, array<string, string>> $files
     * @param string[] $labels
     *
     * @internal
     */
    public function __construct(array $values, array $files, array $labels)
    {
        $this->values = $values;
        $this->files = $files;
        $this->labels = $labels;
    }

    public static function newBuilder(): BugReportBuilder
    {
        return new BugReportBuilder();
    }

    /**
     * @return array<string, string>
     */
    public function getAllValues(): array
    {
        return $this->values;
    }

    public function getValue(string $key, ?string $default = null): ?string
    {
        return $this->values[$key] ?? $default;
    }

    public function hasValue(string $key): bool
    {
        return isset($this->values[$key]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getAllFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, string>
     */
    public function getFiles(string $key): array
    {
        return $this->files[$key];
    }

    public function hasFiles(string $key): bool
    {
        return isset($this->files[$key]);
    }

    public function getFile(string $key, string $filename): string
    {
        return $this->files[$key][$filename];
    }

    public function hasFile(string $key, string $filename): bool
    {
        return isset($this->files[$key][$filename]);
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function hasLabel(string $label): bool
    {
        return \in_array($label, $this->labels, true);
    }
}
