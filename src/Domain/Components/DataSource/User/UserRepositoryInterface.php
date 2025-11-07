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

/**
 * @phpstan-import-type TCriteria from UserTypes
 */
interface UserRepositoryInterface
{
    /**
     * @param TCriteria $criteria
     *
     * @return int
     */
    public function deleteBy(array $criteria): int;

    /**
     * @param int $recordId
     *
     * @return int
     */
    public function deleteById(int $recordId): int;

    /**
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * @param int $recordId
     *
     * @return User|null
     */
    public function findById(int $recordId): ?User;

    /**
     * @param TCriteria $criteria
     *
     * @return User|null
     */
    public function findOneBy(array $criteria): ?User;

    /**
     * @param User $user
     *
     * @return int
     */
    public function insert(User $user): int;

    /**
     * @param User $user
     *
     * @return int
     */
    public function update(User $user): int;
}
