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

namespace Phalcon\Api\Domain\ADR;

use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type THelloInput from InputTypes
 * @phpstan-import-type TUserInput from InputTypes
 */
interface DomainInterface
{
    /**
     * @param THelloInput|TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload;
}
