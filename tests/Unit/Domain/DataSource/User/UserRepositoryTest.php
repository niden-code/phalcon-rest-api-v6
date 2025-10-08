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

namespace Phalcon\Api\Tests\Unit\Domain\DataSource\User;

use Phalcon\Api\Domain\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\DataMapper\Pdo\Connection;

final class UserRepositoryTest extends AbstractUnitTestCase
{
    public function testFind(): void
    {
        $container = new Container();
        /** @var Connection $connection */
        $connection = $container->getShared(Container::CONNECTION);
        /** @var UserRepository $repository */
        $repository = $container->get(Container::REPOSITORY_USER);

        $migration = new UsersMigration($connection);
        $this->setConnection($connection);

        $dbUser = $this->getNewUser($migration);
        $userId = $dbUser['usr_id'];

        $user = $repository->findById($userId);

        $expected = $dbUser['usr_id'];
        $actual   = $user->getId();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_status_flag'];
        $actual   = $user->getStatus();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_username'];
        $actual   = $user->getUsername();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_password'];
        $actual   = $user->getPassword();
        $this->assertSame($expected, $actual);
    }
}
