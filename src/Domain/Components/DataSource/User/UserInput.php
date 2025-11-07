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

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;

use function get_object_vars;

/**
 * @phpstan-import-type TUserInput from InputTypes
 * @phpstan-import-type TUser from UserTypes
 */
final class UserInput
{
    /**
     * @param int|null    $id
     * @param int|null    $status
     * @param string|null $email
     * @param string|null $password
     * @param string|null $namePrefix
     * @param string|null $nameFirst
     * @param string|null $nameMiddle
     * @param string|null $nameLast
     * @param string|null $nameSuffix
     * @param string|null $issuer
     * @param string|null $tokenPassword
     * @param string|null $tokenId
     * @param string|null $preferences
     * @param string|null $createdDate
     * @param int|null    $createdUserId
     * @param string|null $updatedDate
     * @param int|null    $updatedUserId
     */
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

    /**
     * @param SanitizerInterface $sanitizer
     * @param TUserInput         $input
     *
     * @return self
     */
    public static function new(SanitizerInterface $sanitizer, array $input): self
    {
        /** @var TUser $sanitized */
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

    /**
     * @return TUser
     */
    public function toArray(): array
    {
        /** @var TUser $vars */
        $vars = get_object_vars($this);

        return $vars;
    }
}
