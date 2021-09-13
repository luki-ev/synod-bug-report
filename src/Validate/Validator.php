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

use Synod\BugReport\MediaType\MediaTypeGuesser;
use Synod\BugReport\MediaType\MediaTypeGuesserInterface;

final class Validator implements ValidatorInterface
{
    private const PRINTABLE_ASCII_PATTERN = '/^[ -~]*$/';

    /**
     * No control code, but \r, \n, and \t.
     */
    private const TEXT_PATTERN = '/^[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F]*$/u';

    public static int $keyMaxLength = 256;

    public static int $labelMaxCount = 20;

    public static int $labelMaxLength = 256;

    public static int $valueMaxCount = 30;

    public static int $valueMaxLength = 256;

    public static int $textMaxLengths = 1000;

    public static int $filenameMaxLength = 256;

    public static string $filenamePattern = '/^[0-9a-zA-Z-._]+$/';

    public static int $fileMaxCount = 50;

    public static int $fileMaxSize = 1024 * 1024 * 5;

    /**
     * Ensure that listed media types are guessed by $mediaTypeGuesser.
     *
     * @var string[]
     */
    public static array $fileAllowedMediaTypes = [
        'application/gzip',
        'image/png',
        'text/plain',
    ];

    /**
     * @var string[]
     */
    public static array $valueKeysRequired = [
        'text',
        'user_agent',
        'user_id',
        'device',
        'device_id',
    ];

    public static MediaTypeGuesserInterface $mediaTypeGuesser;

    public function __construct()
    {
        if (!isset(static::$mediaTypeGuesser)) {
            static::$mediaTypeGuesser = new MediaTypeGuesser();
        }
    }

    public function validateFile(string $key, string $filename, string $content): void
    {
        $this->validateKey($key);

        Validation::notEmpty($filename, 'A filename must not be empty');
        Validation::maxLength(
            $filename,
            static::$filenameMaxLength,
            sprintf('A filename must not have more than %d characters', static::$filenameMaxLength)
        );
        Validation::regex(
            $filename,
            self::$filenamePattern,
            'Value "%s" is not an allowed filename'
        );

        $propertyPath = "{$key}/{$filename}";
        Validation::lessOrEqualThan(
            \strlen($content),
            static::$fileMaxSize,
            sprintf('A file must not have more than %d bytes', static::$fileMaxSize),
            $propertyPath
        );
        Validation::inMediaTypes(
            static::$mediaTypeGuesser->guessMediaType($content),
            static::$fileAllowedMediaTypes,
            'Media type "%s" is not an allowed media type for files',
            $propertyPath
        );
    }

    public function validateFinalFiles(array $files): void
    {
    }

    public function validateFinalLabels(array $labels): void
    {
        Validation::notEmpty($labels, 'At least one label is required');
    }

    public function validateFinalValues(array $values): void
    {
        foreach (static::$valueKeysRequired as $requiredKey) {
            Validation::keyExists($values, $requiredKey, 'A value for "%s" is missing');
        }
    }

    public function validateLabel(string $label): void
    {
        Validation::notEmpty($label, 'A label must not be empty');
        Validation::maxLength(
            $label,
            static::$labelMaxLength,
            sprintf('A label must not have more than %d characters', static::$labelMaxLength)
        );
        Validation::regex($label, static::PRINTABLE_ASCII_PATTERN, 'A label must only contain printable ASCII characters');
    }

    public function validateValue(string $key, string $value): void
    {
        if ('text' === $key) {
            $this->validateText($value);
        } else {
            $this->validateKey($key);
            Validation::maxLength(
                $value,
                static::$valueMaxLength,
                sprintf('A value for key "%s" must not have more than %d characters', $key, static::$valueMaxLength)
            );
            Validation::regex(
                $value,
                static::PRINTABLE_ASCII_PATTERN,
                sprintf('A value for key "%s" may only contain printable ASCII', $key)
            );
        }
    }

    public function validateFileCount(int $fileCount): void
    {
        Validation::lessOrEqualThan(
            $fileCount,
            static::$fileMaxCount,
            sprintf('More than %d files are not allowed', static::$fileMaxCount)
        );
    }

    public function validateLabelCount(int $labelCount): void
    {
        Validation::lessOrEqualThan($labelCount, static::$labelMaxCount, sprintf('More than %d labels are not allowed', static::$labelMaxCount));
    }

    public function validateValueCount(int $valueCount): void
    {
        Validation::lessOrEqualThan($valueCount, static::$valueMaxCount, sprintf('More than %d values are not allowed', static::$valueMaxCount));
    }

    private function validateKey(string $key): void
    {
        Validation::notEmpty($key, 'A key must not be empty');
        Validation::maxLength(
            $key,
            static::$keyMaxLength,
            sprintf('A key must not have more than %d characters', static::$keyMaxLength)
        );
        Validation::regex($key, static::PRINTABLE_ASCII_PATTERN, 'A key must only contain printable ASCII characters');
    }

    private function validateText(string $text): void
    {
        Validation::maxLength(
            $text,
            static::$textMaxLengths,
            sprintf('A value for key "text" must not have more than %d characters', static::$textMaxLengths)
        );
        Validation::regex(
            $text,
            static::TEXT_PATTERN,
            'A value for key "text" must not contain control code characters, but \r, \n, and \t'
        );
    }
}
