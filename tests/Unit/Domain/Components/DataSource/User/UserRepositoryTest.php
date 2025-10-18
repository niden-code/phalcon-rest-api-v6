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

namespace Phalcon\Api\Tests\Unit\Domain\Components\DataSource\User;

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;

final class UserRepositoryTest extends AbstractUnitTestCase
{
    public function testFindByEmail(): void
    {
        /** @var QueryRepository $repository */
        $repository = $this->container->get(Container::REPOSITORY);

        $migration = new UsersMigration($this->getConnection());

        $repositoryUser = $repository->user()->findByEmail('');
        $this->assertEmpty($repositoryUser);

        $migrationUser = $this->getNewUser($migration);
        $email         = $migrationUser['usr_email'];

        $repositoryUser = $repository->user()->findByEmail($email);

        $this->runAssertions($migrationUser, $repositoryUser);
    }

    public function testFindById(): void
    {
        /** @var QueryRepository $repository */
        $repository = $this->container->get(Container::REPOSITORY);

        $migration = new UsersMigration($this->getConnection());

        $repositoryUser = $repository->user()->findById(0);
        $this->assertEmpty($repositoryUser);

        $migrationUser = $this->getNewUser($migration);
        $userId        = $migrationUser['usr_id'];

        $repositoryUser = $repository->user()->findById($userId);

        $this->runAssertions($migrationUser, $repositoryUser);
    }

    private function runAssertions(array $dbUser, array $user): void
    {
        $expected = $dbUser['usr_id'];
        $actual   = $user['usr_id'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_status_flag'];
        $actual   = $user['usr_status_flag'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_email'];
        $actual   = $user['usr_email'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_password'];
        $actual   = $user['usr_password'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_prefix'];
        $actual   = $user['usr_name_prefix'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_first'];
        $actual   = $user['usr_name_first'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_middle'];
        $actual   = $user['usr_name_middle'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_last'];
        $actual   = $user['usr_name_last'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_suffix'];
        $actual   = $user['usr_name_suffix'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_issuer'];
        $actual   = $user['usr_issuer'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_password'];
        $actual   = $user['usr_token_password'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_id'];
        $actual   = $user['usr_token_id'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_preferences'];
        $actual   = $user['usr_preferences'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_date'];
        $actual   = $user['usr_created_date'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_usr_id'];
        $actual   = $user['usr_created_usr_id'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_date'];
        $actual   = $user['usr_updated_date'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_usr_id'];
        $actual   = $user['usr_updated_usr_id'];
        $this->assertSame($expected, $actual);
    }
}
