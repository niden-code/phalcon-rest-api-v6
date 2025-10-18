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

namespace Phalcon\Api\Domain\Services\Hello;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\DomainInterface;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Domain\Payload;

use function date;

/**
 * @phpstan-import-type THelloInput from InputTypes
 */
final class HelloService implements DomainInterface
{
    /**
     * @param THelloInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'data' => [
                    "Hello World!!! - " . date(Dates::DATE_TIME_FORMAT),
                ],
            ]
        );
    }
}
