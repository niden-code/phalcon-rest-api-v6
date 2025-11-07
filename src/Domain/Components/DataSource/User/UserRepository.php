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

use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\DataSource\AbstractRepository;
use Phalcon\Api\Domain\Components\Enums\Common\FlagsEnum;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Select;
use Phalcon\DataMapper\Query\Update;

use function array_filter;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 *
 * The 'final' keyword was intentionally removed from this class to allow
 * extension for testing purposes (e.g., mocking in unit tests).
 *
 * Please avoid extending this class in production code unless absolutely necessary.
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
        private readonly UserMapper $mapper,
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
                    'usr_email' => $email,
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
     * @param array<string, bool|int|string|null> $criteria
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
     * @param User $user
     *
     * @return int
     */
    public function insert(User $user): int
    {
        $row = $this->mapper->db($user);
        $now = Dates::toUTC(format: Dates::DATE_TIME_FORMAT);

        /**
         * @todo this should not be here - the insert should just add data not validate
         */
        if (true === empty($row['usr_created_date'])) {
            $row['usr_created_date'] = $now;
        }
        if (true === empty($row['usr_updated_date'])) {
            $row['usr_updated_date'] = $now;
        }

        /**
         * Remove usr_id just in case
         */
        unset($row['usr_id']);

        /**
         * Cleanup empty fields if needed
         */
        $columns = $this->cleanupFields($row);
        $insert  = Insert::new($this->connection);
        $insert
            ->into($this->table)
            ->columns($columns)
            ->perform()
        ;

        return (int)$insert->getLastInsertId();
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function update(User $user): int
    {
        $row    = $this->mapper->db($user);
        $now    = Dates::toUTC(format: Dates::DATE_TIME_FORMAT);
        $userId = $row['usr_id'];
        /**
         * @todo this should not be here - the update should just add data not validate
         */
        /**
         * Set updated date to now if it has not been set
         */
        if (true === empty($row['usr_updated_date'])) {
            $row['usr_updated_date'] = $now;
        }

        /**
         * Remove createdDate and createdUserId - cannot be changed. This
         * needs to be here because we don't want to touch those fields.
         */
        unset($row['usr_created_date'], $row['usr_created_usr_id']);

        /**
         * Cleanup empty fields if needed
         */
        $columns = $this->cleanupFields($row);
        $update  = Update::new($this->connection);
        $update
            ->table($this->table)
            ->columns($columns)
            ->where('usr_id = ', $userId)
            ->perform()
        ;

        return $userId;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    private function cleanupFields(array $row): array
    {
        unset($row['usr_id']);

        return array_filter(
            $row,
            static fn($v) => $v !== null && $v !== ''
        );
    }
}
