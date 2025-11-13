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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\User\Repositories;

use Phalcon\Api\Domain\Infrastructure\DataSource\AbstractRepository;
use Phalcon\Api\Domain\Infrastructure\DataSource\Interfaces\MapperInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\DTO\User;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Infrastructure\Enums\Common\FlagsEnum;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Select;
use Phalcon\DataMapper\Query\Update;

/**
 * @phpstan-import-type TCriteria from UserTypes
 * @phpstan-import-type TUserRecord from UserTypes
 * @phpstan-import-type TUserDbRecordOptional from UserTypes
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * @var string
     */
    protected string $idField = 'usr_id';
    /**
     * @var string
     */
    protected string $table = 'co_users';

    public function __construct(
        Connection $connection,
        private readonly MapperInterface $mapper,
    ) {
        parent::__construct($connection);
    }


    /**
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        if (true !== empty($email)) {
            return $this->findOneBy(
                [
                    'usr_email'       => $email,
                    'usr_status_flag' => FlagsEnum::Active->value,
                ]
            );
        }

        return null;
    }

    /**
     * @param int $recordId
     *
     * @return User|null
     */
    public function findById(int $recordId): ?User
    {
        if ($recordId > 0) {
            return $this->findOneBy(
                [
                    $this->idField => $recordId,
                ]
            );
        }

        return null;
    }


    /**
     * @param TCriteria $criteria
     *
     * @return User|null
     */
    public function findOneBy(array $criteria): ?User
    {
        $select = Select::new($this->connection);

        /** @var TUserRecord $result */
        $result = $select
            ->from($this->table)
            ->whereEquals($criteria)
            ->fetchOne()
        ;

        if (empty($result)) {
            return null;
        }

        return $this->mapper->domain($result);
    }


    /**
     *
     * @param TUserDbRecordOptional $columns
     *
     * @return int
     */
    public function insert(array $columns): int
    {
        $insert = Insert::new($this->connection);
        $insert
            ->into($this->table)
            ->columns($columns)
            ->perform()
        ;

        return (int)$insert->getLastInsertId();
    }

    /**
     * @param int                   $recordId
     * @param TUserDbRecordOptional $columns
     *
     * @return int
     */
    public function update(int $recordId, array $columns): int
    {
        $update = Update::new($this->connection);
        $update
            ->table($this->table)
            ->columns($columns)
            ->where($this->idField . ' = ', $recordId)
            ->perform()
        ;

        return $recordId;
    }
}
