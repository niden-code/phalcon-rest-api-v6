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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\User\Validators;

use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\DTO\AuthInput;
use Phalcon\Api\Domain\Infrastructure\DataSource\Validation\Result;

trait UserValidatorTrait
{
    /**
     * Validate a AuthInput and return an array of errors.
     * Empty array means valid.
     *
     * @param AuthInput $input
     *
     * @return Result
     */
    public function validate(mixed $input): Result
    {
        $errors = $this->runValidations($input);
        if (true !== empty($errors)) {
            return Result::error($errors);
        }

        return Result::success();
    }

    /**
     * @param AuthInput $input
     *
     * @return array<array-key, string>
     */
    abstract protected function runValidations(mixed $input): array;
}
