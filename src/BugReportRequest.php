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

use Assert\Assertion;
use Riverline\MultiPartParser\StreamedPart;
use Symfony\Component\HttpFoundation\Request;
use Synod\BugReport\Exception\InvalidRequestException;

class BugReportRequest extends Request
{
    private ?StreamedPart $formMultiPart = null;

    /**
     * @throws InvalidRequestException
     */
    public function getFormMultiPart(): StreamedPart
    {
        return $this->formMultiPart ??= $this->parseFormMultiPart();
    }

    /**
     * @throws InvalidRequestException
     */
    private function parseFormMultiPart(): StreamedPart
    {
        $contentType = $this->headers->get('content-type');
        Assertion::notEmpty($contentType, 'Content-Type header missing');

        $stream = fopen('php://temp', 'rw');
        Assertion::isResource($stream);
        fwrite($stream, "content-type: {$contentType}\r\n");
        fwrite($stream, "\r\n");

        $content = $this->getContent(true);
        Assertion::isResource($content);
        stream_copy_to_stream($content, $stream);
        rewind($stream);

        try {
            return new StreamedPart($stream);
        } catch (\InvalidArgumentException|\LogicException $e) {
            // LogicExcepttion is required, see https://github.com/Riverline/multipart-parser/pull/38
            throw new InvalidRequestException(sprintf('Could not parse request content: %s', $e->getMessage()), $e->getCode(), $e);
        }
    }
}
