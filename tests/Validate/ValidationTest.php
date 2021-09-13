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

namespace Synod\BugReport\Tests\Validate;

use PHPUnit\Framework\TestCase;
use Synod\BugReport\Exception\ValidationException;
use Synod\BugReport\Validate\Validation;

/**
 * @covers \Synod\BugReport\Validate\Validation
 */
final class ValidationTest extends TestCase
{
    /**
     * @covers \Synod\BugReport\Validate\Validation::inMediaTypes
     */
    public function testInMediaTypes(): void
    {
        Validation::inMediaTypes('text/plain', ['image/png', 'text/plain']);
        Validation::inMediaTypes('text/plain', ['text/*']);
        Validation::inMediaTypes('text/plain', ['*/*']);

        /** @phpstan-ignore-next-line (assertion is required for coverage) */
        static::assertTrue(true);
    }

    /**
     * @covers \Synod\BugReport\Validate\Validation::inMediaTypes
     */
    public function testNotInMediaTypes(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('"text/plain" does not match any of the allowed media types.');
        $this->expectExceptionCode(Validation::INVALID_MEDIA_TYPE);
        Validation::inMediaTypes('text/plain', ['image/png']);
    }
}
