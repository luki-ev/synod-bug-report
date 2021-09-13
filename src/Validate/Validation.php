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

use Assert\Assertion;
use Synod\BugReport\Exception\ValidationException;

class Validation extends Assertion
{
    public const INVALID_MEDIA_TYPE = 1000;

    protected static $exceptionClass = ValidationException::class;

    /**
     * Assert that the value matches one of the given media types.
     *
     * @param mixed $value
     * @param string[] $mediaTypes
     * @param callable(array<string, mixed>):string|null|string $message
     *
     * @throws ValidationException
     */
    public static function inMediaTypes($value, array $mediaTypes, string|callable|null $message = null, ?string $propertyPath = null): bool
    {
        static::string($value, null, $propertyPath);
        foreach ($mediaTypes as $allowedMediaType) {
            if (static::matchesMediaType($value, $allowedMediaType)) {
                return true;
            }
        }

        $message = sprintf(
            static::generateMessage($message ?? '"%s" does not match any of the allowed media types.'),
            $value
        );

        throw static::createException($value, $message, static::INVALID_MEDIA_TYPE, $propertyPath, ['expected' => $mediaTypes]);
    }

    private static function matchesMediaType(string $value, string $mediaType): bool
    {
        return fnmatch($mediaType, $value, FNM_PATHNAME);
    }
}
