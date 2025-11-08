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

use Phalcon\Api\Domain\Components\DataSource\Validation\AbstractValidator;
use Phalcon\Api\Domain\Components\Enums\Input\UserInputUpdateEnum;

final class UserValidatorUpdate extends AbstractValidator
{
    use UserValidatorTrait;

    protected string $fields = UserInputUpdateEnum::class;
}
