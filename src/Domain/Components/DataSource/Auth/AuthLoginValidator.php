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

use Phalcon\Api\Domain\Components\DataSource\Validation\Result;
use Phalcon\Api\Domain\Components\DataSource\Validation\ValidatorInterface;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;

final class AuthLoginValidator implements ValidatorInterface
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
        /** @var AuthInput $input */
        if (true === empty($input->email) || true === empty($input->password)) {
            return Result::error(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        return Result::success();
    }
}
