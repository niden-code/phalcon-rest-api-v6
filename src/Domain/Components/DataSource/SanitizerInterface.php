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

interface SanitizerInterface
{
    /**
     * Return a sanitized array of the input
     *
     * @param array $input
     *
     * @return array
     */
    public function sanitize(array $input): array;
}
