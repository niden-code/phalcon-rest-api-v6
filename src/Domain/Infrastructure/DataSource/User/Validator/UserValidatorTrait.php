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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\User\Validator;

use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\Validation\Result;

trait UserValidatorTrait
{
    /**
     * Validate a AuthInput and return an array of errors.
     * Empty array means valid.
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function validate(CommandInterface $command): Result
    {
        $errors = $this->runValidations($command);
        if (true !== empty($errors)) {
            return Result::error($errors);
        }

        return Result::success();
    }

    /**
     * @param CommandInterface $command
     *
     * @return array<array-key, string>
     */
    abstract protected function runValidations(CommandInterface $command): array;
}
