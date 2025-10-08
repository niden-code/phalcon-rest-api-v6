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

namespace Phalcon\Api\Domain\DataSource\User;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\Select;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 */
final class UserRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param int|string $userId
     *
     * @return UserTransport
     */
    public function findById(int | string $userId): UserTransport
    {
        $result = [];
        if (true !== empty($userId)) {
            $select = Select::new($this->connection);

            /** @var TUserRecord $result */
            $result = $select
                ->from('co_users')
                ->where('usr_id = ', $userId)
                ->fetchOne()
            ;
        }

        return new UserTransport($result);
    }
}
