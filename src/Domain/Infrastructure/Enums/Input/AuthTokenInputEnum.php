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

use Phalcon\Filter\Validation\Validator\PresenceOf;

enum AuthTokenInputEnum implements ValidatorEnumInterface
{
    case token;

    public function validators(): array
    {
        return [PresenceOf::class];
    }
}
