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

use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;

final class AuthInput
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $token
    ) {
    }

    public static function new(SanitizerInterface $sanitizer, array $input): self
    {
        $sanitized = $sanitizer->sanitize($input);

        return new self(
            $sanitized['email'] ?? null,
            $sanitized['password'] ?? null,
            $sanitized['token'] ?? null,
        );
    }
}
