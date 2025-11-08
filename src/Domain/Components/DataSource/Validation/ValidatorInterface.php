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

namespace Phalcon\Api\Domain\Components\DataSource\Validation;

/**
 * Validator contract. Accepts a DTO or input and returns a Result.
 */
interface ValidatorInterface
{
    /**
     * Validate a DTO or input structure.
     *
     * @param mixed $input DTO or array
     *
     * @return Result
     */
    public function validate(mixed $input): Result;
}
