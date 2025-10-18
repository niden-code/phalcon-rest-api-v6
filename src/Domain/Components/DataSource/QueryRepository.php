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

use Phalcon\Api\Domain\Components\DataSource\User\UserRepository;
use Phalcon\DataMapper\Pdo\Connection;

class QueryRepository
{
    private ?UserRepository $user = null;

    /**
     * @param Connection $connection
     */
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return UserRepository
     */
    public function user(): UserRepository
    {
        if (null === $this->user) {
            $this->user = new UserRepository($this->connection);
        }

        return $this->user;
    }
}
