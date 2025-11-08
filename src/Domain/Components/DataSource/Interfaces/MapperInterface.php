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

namespace Phalcon\Api\Domain\Components\DataSource\Interfaces;

use Phalcon\Api\Domain\Components\DataSource\User\User;
use Phalcon\Api\Domain\Components\DataSource\User\UserInput;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;

/**
 * Contract for mapping between domain DTO/objects and persistence arrays.
 *
 * @phpstan-import-type TUser from UserTypes
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-import-type TUserDomainToDbRecord from UserTypes
 */
interface MapperInterface
{
    /**
     * Map Domain User -> DB row (usr_*)
     *
     * @return TUserDomainToDbRecord
     */
    public function db(UserInput $user): array;

    /**
     * Map DB row (usr_*) -> Domain User
     *
     * @param TUserDbRecord|array{} $row
     */
    public function domain(array $row): User;

    /**
     * Map input row -> Domain User
     *
     * @param TUser $row
     */
    public function input(array $row): User;
}
