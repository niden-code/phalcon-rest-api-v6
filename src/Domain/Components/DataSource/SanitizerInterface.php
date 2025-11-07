<?php

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Api\Domain\Components\DataSource;

/**
 * @phpstan-type TInput array<string, bool|int|string|null>
 */
interface SanitizerInterface
{
    /**
     * Return a sanitized array of the input
     *
     * @param TInput $input
     *
     * @return TInput
     */
    public function sanitize(array $input): array;
}
