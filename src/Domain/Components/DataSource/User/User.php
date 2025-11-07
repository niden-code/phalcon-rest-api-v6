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

use function get_object_vars;
use function trim;

/**
 * @phpstan-import-type TUser from UserTypes
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly int $status,
        public readonly string $email,
        public readonly string $password,
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

    public function fullName(): string
    {
        return trim(
            ($this->nameLast ?? '') . ', ' . ($this->nameFirst ?? '') . ' ' . ($this->nameMiddle ?? '')
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
