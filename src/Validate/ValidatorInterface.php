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

namespace Synod\BugReport\Validate;

use Synod\BugReport\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * @throws ValidationException
     */
    public function validateFile(string $key, string $filename, string $content): void;

    /**
     * @throws ValidationException
     */
    public function validateFileCount(int $fileCount): void;

    /**
     * @throws ValidationException
     */
    public function validateLabel(string $label): void;

    /**
     * @throws ValidationException
     */
    public function validateLabelCount(int $labelCount): void;

    /**
     * @throws ValidationException
     */
    public function validateValue(string $key, string $value): void;

    /**
     * @throws ValidationException
     */
    public function validateValueCount(int $valueCount): void;

    /**
     * @param array<string, array<string, string>> $files
     *
     * @throws ValidationException
     */
    public function validateFinalFiles(array $files): void;

    /**
     * @param string[] $labels
     *
     * @throws ValidationException
     */
    public function validateFinalLabels(array $labels): void;

    /**
     * @param array<string, string> $values
     *
     * @throws ValidationException
     */
    public function validateFinalValues(array $values): void;
}
