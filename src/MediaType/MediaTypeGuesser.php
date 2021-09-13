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

namespace Synod\BugReport\MediaType;

final class MediaTypeGuesser implements MediaTypeGuesserInterface
{
    private const MAGIC = [
        'application/gzip' => "\x1F\x8B",
        'image/png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
    ];

    private const NO_CONTROL_CODE_PATTERN = '/^[^\x00-\x1F\x7F]*$/u';

    public function guessMediaType(string $content): string
    {
        foreach (static::MAGIC as $mediaType => $magic) {
            if (str_starts_with($content, $magic)) {
                return $mediaType;
            }
        }

        if (1 === preg_match(static::NO_CONTROL_CODE_PATTERN, $content)) {
            // UTF-8 without control code
            return 'text/plain';
        }

        return 'application/octet-stream';
    }
}
