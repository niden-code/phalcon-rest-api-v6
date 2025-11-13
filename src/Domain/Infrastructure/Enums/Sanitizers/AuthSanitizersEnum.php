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

namespace Phalcon\Api\Domain\Infrastructure\Enums\Sanitizers;

use Phalcon\Filter\Filter;

enum AuthSanitizersEnum implements SanitizersEnumInterface
{
    case email;
    case password;
    case token;

    public function default(): mixed
    {
        return null;
    }

    public function sanitizer(): string
    {
        return match ($this) {
            self::email => Filter::FILTER_EMAIL,
            default     => ''
        };
    }
}
