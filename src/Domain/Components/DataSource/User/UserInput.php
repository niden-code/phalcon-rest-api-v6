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

use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;

final class UserInput
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $status,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $namePrefix,
        public readonly ?string $nameFirst,
        public readonly ?string $nameMiddle,
        public readonly ?string $nameLast,
        public readonly ?string $nameSuffix,
        public readonly ?string $issuer,
        public readonly ?string $tokenPassword,
        public readonly ?string $tokenId,
        public readonly ?string $preferences,
        public readonly ?string $createdDate,
        public readonly ?int $createdUserId,
        public readonly ?string $updatedDate,
        public readonly ?int $updatedUserId,
    ) {
    }

    public static function new(SanitizerInterface $sanitizer, array $input): self
    {
        $sanitized = $sanitizer->sanitize($input);

        return new self(
            $sanitized['id'],
            $sanitized['status'],
            $sanitized['email'],
            $sanitized['password'],
            $sanitized['namePrefix'],
            $sanitized['nameFirst'],
            $sanitized['nameMiddle'],
            $sanitized['nameLast'],
            $sanitized['nameSuffix'],
            $sanitized['issuer'],
            $sanitized['tokenPassword'],
            $sanitized['tokenId'],
            $sanitized['preferences'],
            $sanitized['createdDate'],
            $sanitized['createdUserId'],
            $sanitized['updatedDate'],
            $sanitized['updatedUserId']
        );
    }

    public function toArray(): array
    {
        return [$this->id => get_object_vars($this)];
    }
}
