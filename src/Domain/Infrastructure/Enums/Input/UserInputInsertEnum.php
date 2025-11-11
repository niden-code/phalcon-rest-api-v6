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

namespace Phalcon\Api\Domain\Infrastructure\Enums\Input;

use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Validator\PresenceOf;

enum UserInputInsertEnum implements ValidatorEnumInterface
{
    case email;
    case password;
    case issuer;
    case tokenPassword;
    case tokenId;

    public function validators(): array
    {
        return match ($this) {
            self::email    => [
                PresenceOf::class,
                Email::class
            ],
            default => [PresenceOf::class],
        };
    }
}
