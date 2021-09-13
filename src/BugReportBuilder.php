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

use Synod\BugReport\Exception\ValidationException;
use Synod\BugReport\Validate\Validation;
use Synod\BugReport\Validate\Validator;
use Synod\BugReport\Validate\ValidatorInterface;

final class BugReportBuilder
{
    private static ValidatorInterface $validator;

    /**
     * @var array<string, string>
     */
    private array $values = [];

    /**
     * @var array<string, array<string, string>>
     */
    private array $files = [];

    private int $fileCount = 0;

    /**
     * @var string[]
     */
    private array $labels = [];

    public function __construct()
    {
        if (!isset(static::$validator)) {
            static::$validator = new Validator();
        }
    }

    public static function setValidator(ValidatorInterface $validator): void
    {
        static::$validator = $validator;
    }

    /**
     * @throws ValidationException
     *
     * @return $this
     */
    public function add(string $key, string $value): static
    {
        if ('label' === $key) {
            $this->addLabel($value);
        } else {
            $this->addValue($key, $value);
        }

        return $this;
    }

    /**
     * @throws ValidationException
     *
     * @return $this
     */
    public function addFile(string $key, string $filename, string $content): static
    {
        static::$validator->validateFileCount($this->fileCount + 1);
        static::$validator->validateFile($key, $filename, $content);

        Validation::false(isset($this->files[$key][$filename]), sprintf(
            'Only one file is allowed for the combination of key "%s" and filename "%s"',
            $key,
            $filename
        ));

        $this->files[$key][$filename] = $content;
        ++$this->fileCount;

        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function build(): BugReport
    {
        static::$validator->validateFinalFiles($this->files);
        static::$validator->validateFinalLabels($this->labels);
        static::$validator->validateFinalValues($this->values);

        return new BugReport($this->values, $this->files, $this->labels);
    }

    /**
     * @throws ValidationException
     */
    private function addLabel(string $label): void
    {
        static::$validator->validateLabelCount(\count($this->labels) + 1);
        static::$validator->validateLabel($label);

        $this->labels[] = $label;
    }

    /**
     * @throws ValidationException
     */
    private function addValue(string $key, string $value): void
    {
        static::$validator->validateValueCount(\count($this->values) + 1);
        static::$validator->validateValue($key, $value);

        Validation::keyNotExists($this->values, $key, 'Only one value is allowed for key "%s"');

        $this->values[$key] = $value;
    }
}
