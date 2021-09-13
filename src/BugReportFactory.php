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

final class BugReportFactory
{
    public static function new(): static
    {
        return new static();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function createFromFormMultiPart(StreamedPart $formMultiPart): BugReport
    {
        Assertion::true($formMultiPart->isMultiPart(), 'Expected a multi-part element');

        $builder = BugReport::newBuilder();
        foreach ($formMultiPart->getParts() as $part) {
            Assertion::false($part->isMultiPart(), sprintf('Unexpected multi-part element with name "%s"', $part->getName()));
            Assertion::notBlank($part->getName(), 'Header option name in part element is missing');
            Assertion::string($part->getName());

            if ($part->isFile()) {
                Assertion::notBlank($part->getFileName(), sprintf('Header option filename in part element with name "%s" is missing', $part->getName()));
                Assertion::string($part->getFileName());
                $builder->addFile($part->getName(), $part->getFileName(), $part->getBody());
            } else {
                $builder->add($part->getName(), $part->getBody());
            }
        }

        return $builder->build();
    }
}
