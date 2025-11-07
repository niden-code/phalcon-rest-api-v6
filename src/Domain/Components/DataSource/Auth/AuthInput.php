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

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;

/**
 * @phpstan-import-type TAuthInput from InputTypes
 */
final class AuthInput
{
    /**
     * @param string|null $email
     * @param string|null $password
     * @param string|null $token
     */
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $token
    ) {
    }

    /**
     * @param SanitizerInterface $sanitizer
     * @param TAuthInput         $input
     *
     * @return self
     */
    public static function new(SanitizerInterface $sanitizer, array $input): self
    {
        $sanitized = $sanitizer->sanitize($input);

        /** @var string|null $email */
        $email = $sanitized['email'] ?? null;
        /** @var string|null $password */
        $password = $sanitized['password'] ?? null;
        /** @var string|null $token */
        $token = $sanitized['token'] ?? null;

        return new self($email, $password, $token);
    }
}
