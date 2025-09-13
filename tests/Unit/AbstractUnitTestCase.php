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

namespace Phalcon\Api\Tests\Unit;

use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * Return a long series of strings to be used as a password
     *
     * @return string
     */
    public function getStrongPassword(): string
    {
        return substr(base64_encode(random_bytes(512)), 0, 128);
    }
}
