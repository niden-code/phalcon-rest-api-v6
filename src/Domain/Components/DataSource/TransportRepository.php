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

namespace Phalcon\Api\Domain\Components\DataSource;

use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 */
final readonly class TransportRepository
{
    /**
     * @param TUserRecord $dbUser
     *
     * @return UserTransport
     */
    public function newUser(array $dbUser): UserTransport
    {
        return new UserTransport($dbUser);
    }
}
