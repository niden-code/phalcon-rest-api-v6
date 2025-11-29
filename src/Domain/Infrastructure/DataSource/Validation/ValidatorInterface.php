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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\Validation;

use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandInterface;

/**
 * Validator contract. Accepts a DTO or input and returns a Result.
 */
interface ValidatorInterface
{
    /**
     * Validate a DTO or input structure.
     *
     * @param CommandInterface $command DTO or array
     *
     * @return Result
     */
    public function validate(CommandInterface $command): Result;
}
