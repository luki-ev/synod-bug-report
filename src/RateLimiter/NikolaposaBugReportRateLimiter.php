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

namespace Synod\BugReport\RateLimiter;

use Assert\Assertion;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RateLimiter;
use Synod\BugReport\BugReportRequest;

final class NikolaposaBugReportRateLimiter implements BugReportRateLimiterInterface
{
    private RateLimiter $rateLimiter;

    /**
     * @var Rate[]
     */
    private array $globalRates;

    /**
     * @var Rate[]
     */
    private array $ipRates;

    /**
     * @param Rate[] $globalRates
     * @param Rate[] $ipRates
     */
    public function __construct(RateLimiter $rateLimiter, array $globalRates, array $ipRates)
    {
        $this->rateLimiter = $rateLimiter;
        $this->globalRates = static::sortRates($globalRates);
        $this->ipRates = static::sortRates($ipRates);
    }

    public function limit(BugReportRequest $request): bool
    {
        try {
            $ip = $request->getClientIp();
            Assertion::notNull($ip, 'No client IP');
            foreach ($this->ipRates as $rate) {
                $this->rateLimiter->limit($ip, $rate);
            }

            foreach ($this->globalRates as $rate) {
                $this->rateLimiter->limit('global', $rate);
            }

            return true;
        } catch (LimitExceeded $ignored) {
            return false;
        }
    }

    /**
     * @param Rate[] $rates
     *
     * @return Rate[]
     */
    private static function sortRates(array $rates): array
    {
        usort($rates, fn (Rate $rate1, Rate $rate2) => $rate1->getInterval() - $rate2->getInterval());

        return $rates;
    }
}
