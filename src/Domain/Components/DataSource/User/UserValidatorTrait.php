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

namespace Phalcon\Api\Domain\Components\DataSource\User;

use Phalcon\Api\Domain\Components\DataSource\Auth\AuthInput;
use Phalcon\Api\Domain\Components\DataSource\Validation\AbstractValidator;
use Phalcon\Api\Domain\Components\DataSource\Validation\Result;
use Phalcon\Api\Domain\Components\Enums\Input\UserInputInsertEnum;

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
