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

namespace Phalcon\Api\Domain\Hello;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\Interfaces\DomainInterface;
use Phalcon\Domain\Payload;

use function date;

final class HelloService implements DomainInterface
{
    public function __invoke(): Payload
    {
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'results' => "Hello World!!! - " . date("Y-m-d H:i:s"),
            ]
        );
    }
}
