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

use Phalcon\Api\Domain\Components\Enums\Common\FlagsEnum;
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
     * @param string $email
     *
     * @return TUserRecord
     */
    public function findByEmail(string $email): array
    {
        $result = [];
        if (true !== empty($email)) {
            return $this->findOneBy(
                [
                    'usr_email'       => $email,
                    'usr_status_flag' => FlagsEnum::Active->value,
                ]
            );
        }

        return $result;
    }

    /**
     * @param int $userId
     *
     * @return TUserRecord
     */
    public function findById(int $userId): array
    {
        $result = [];
        if ($userId > 0) {
            return $this->findOneBy(
                [
                    'usr_id' => $userId,
                ]
            );
        }

        return $result;
    }

    /**
     * @param array<string, bool|int|string|null> $criteria
     *
     * @return TUserRecord
     */
    public function findOneBy(array $criteria): array
    {
        $select = Select::new($this->connection);

        /** @var TUserRecord $result */
        $result = $select
            ->from('co_users')
            ->whereEquals($criteria)
            ->fetchOne()
        ;

        return $result;
    }
}
