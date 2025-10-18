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

namespace Phalcon\Api\Tests\Fixtures\Domain\Services;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\DomainInterface;
use Phalcon\Domain\Payload;

final readonly class ServiceFixture implements DomainInterface
{
    public function __invoke(array $input): Payload
    {
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'data' => $input,
            ]
        );
    }
}
