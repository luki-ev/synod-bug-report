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

namespace Synod\BugReport\Tests\MediaType;

use Assert\Assertion;
use PHPUnit\Framework\TestCase;
use Synod\BugReport\MediaType\MediaTypeGuesser;

/**
 * @covers \Synod\BugReport\MediaType\MediaTypeGuesser
 */
final class MediaTypeGuesserTest extends TestCase
{
    /**
     * @var MediaTypeGuesser
     */
    private $mediaTypeGuesser;

    protected function setUp(): void
    {
        $this->mediaTypeGuesser = new MediaTypeGuesser();
    }

    /**
     * @covers \Synod\BugReport\MediaType\MediaTypeGuesser::guessMediaType
     */
    public function testGuessMediaTypePng(): void
    {
        $png = file_get_contents(__DIR__.'/../_data/image.png');
        Assertion::string($png);
        static::assertSame('image/png', $this->mediaTypeGuesser->guessMediaType($png));
    }

    /**
     * @covers \Synod\BugReport\MediaType\MediaTypeGuesser::guessMediaType
     */
    public function testGuessMediaTypeGzip(): void
    {
        $gzip = file_get_contents(__DIR__.'/../_data/empty.gz');
        Assertion::string($gzip);
        static::assertSame('application/gzip', $this->mediaTypeGuesser->guessMediaType($gzip));
    }

    /**
     * @covers \Synod\BugReport\MediaType\MediaTypeGuesser::guessMediaType
     */
    public function testGuessMediaTypeTextPlain(): void
    {
        static::assertSame('text/plain', $this->mediaTypeGuesser->guessMediaType('test'));
        static::assertSame('text/plain', $this->mediaTypeGuesser->guessMediaType(''));
    }

    /**
     * @covers \Synod\BugReport\MediaType\MediaTypeGuesser::guessMediaType
     */
    public function testGuessMediaTypeBinary(): void
    {
        static::assertSame('application/octet-stream', $this->mediaTypeGuesser->guessMediaType("\0"));
    }
}
