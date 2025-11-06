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

use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\Delete;
use Phalcon\DataMapper\Query\Select;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 */
abstract class AbstractRepository
{
    /**
     * @var string
     */
    protected string $idField = '';
    /**
     * @var string
     */
    protected string $table = '';

    public function __construct(
        protected readonly Connection $connection,
    ) {
    }

    /**
     * @param array<string, bool|int|string|null> $criteria
     *
     * @return int
     */
    public function deleteBy(array $criteria): int
    {
        $delete = Delete::new($this->connection);

        $statement = $delete
            ->table($this->table)
            ->whereEquals($criteria)
            ->perform()
        ;

        return $statement->rowCount();
    }

    /**
     * @param int $recordId
     *
     * @return int
     */
    public function deleteById(int $recordId): int
    {
        return $this->deleteBy(
            [
                $this->idField => $recordId,
            ]
        );
    }

    /**
     * @param int $recordId
     *
     * @return TUserRecord
     */
    public function findById(int $recordId): array
    {
        $result = [];
        if ($recordId > 0) {
            return $this->findOneBy(
                [
                    $this->idField => $recordId,
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
            ->from($this->table)
            ->whereEquals($criteria)
            ->fetchOne()
        ;

        return $result;
    }
}
