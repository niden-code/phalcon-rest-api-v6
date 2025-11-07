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

use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepositoryInterface;
use Phalcon\DataMapper\Pdo\Connection;

class QueryRepository
{
    private ?UserRepositoryInterface $user = null;

    /**
     * @param Connection $connection
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly UserMapper $userMapper,
    ) {
    }

    /**
     * @return UserRepository
     */
    public function user(): UserRepositoryInterface
    {
        if (null === $this->user) {
            $this->user = new UserRepository($this->connection, $this->userMapper);
        }

        return $this->user;
    }
}
