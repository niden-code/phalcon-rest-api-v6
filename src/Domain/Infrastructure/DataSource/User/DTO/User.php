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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\User\DTO;

use Phalcon\Api\Domain\Infrastructure\DataSource\User\UserTypes;

use function get_object_vars;
use function trim;

/**
 * @phpstan-import-type TUser from UserTypes
 */
final readonly class User
{
    public function __construct(
        public int $id,
        public int $status,
        public string $email,
        public string $password,
        public ?string $namePrefix,
        public ?string $nameFirst,
        public ?string $nameMiddle,
        public ?string $nameLast,
        public ?string $nameSuffix,
        public ?string $issuer,
        public ?string $tokenPassword,
        public ?string $tokenId,
        public ?string $preferences,
        public ?string $createdDate,
        public ?int $createdUserId,
        public ?string $updatedDate,
        public ?int $updatedUserId,
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
