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

namespace Phalcon\Api\Domain\Components\DataSource\Auth;

use Phalcon\Api\Domain\Components\DataSource\Validation\AbstractValidator;
use Phalcon\Api\Domain\Components\DataSource\Validation\Result;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Enums\Input\AuthLoginInputEnum;

final class AuthLoginValidator extends AbstractValidator
{
    protected string $fields = AuthLoginInputEnum::class;

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
            return Result::error(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        return Result::success();
    }
}
