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

namespace Phalcon\Api\Tests\Unit\Domain\Services\User;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Services\User\UserGetService;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class UserServiceGetTest extends AbstractUnitTestCase
{
    public function testServiceEmptyUserId(): void
    {
        /** @var UserGetService $service */
        $service = $this->container->get(Container::USER_GET_SERVICE);

        $payload = $service->__invoke([]);

        $expected = DomainStatus::NOT_FOUND;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $expected = 'Record(s) not found';
        $actual   = $actual['errors'][0];
        $this->assertStringContainsString($expected, $actual);
    }

    public function testServiceWithUserId(): void
    {
        /** @var UserGetService $service */
        $service = $this->container->get(Container::USER_GET_SERVICE);

        $migration = new UsersMigration($this->getConnection());
        $dbUser    = $this->getNewUser($migration);
        $userId    = $dbUser['usr_id'];

        $payload = $service->__invoke(
            [
                'id' => $userId,
            ]
        );

        $expected = DomainStatus::SUCCESS;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $user = $actual['data'];
        $key  = array_key_first($user);
        $user = $user[$key];

        $expected = $dbUser['usr_id'];
        $actual   = $user['id'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_status_flag'];
        $actual   = $user['status'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_email'];
        $actual   = $user['email'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_password'];
        $actual   = $user['password'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_prefix'];
        $actual   = $user['namePrefix'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_first'];
        $actual   = $user['nameFirst'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_middle'];
        $actual   = $user['nameMiddle'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_last'];
        $actual   = $user['nameLast'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_suffix'];
        $actual   = $user['nameSuffix'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_issuer'];
        $actual   = $user['issuer'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_password'];
        $actual   = $user['tokenPassword'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_id'];
        $actual   = $user['tokenId'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_preferences'];
        $actual   = $user['preferences'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_date'];
        $actual   = $user['createdDate'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_usr_id'];
        $actual   = $user['createdUserId'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_date'];
        $actual   = $user['updatedDate'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_usr_id'];
        $actual   = $user['updatedUserId'];
        $this->assertSame($expected, $actual);
    }

    public function testServiceWrongUserId(): void
    {
        /** @var UserGetService $service */
        $service = $this->container->get(Container::USER_GET_SERVICE);

        $payload = $service->__invoke(
            [
                'id' => 999999,
            ]
        );

        $expected = DomainStatus::NOT_FOUND;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $expected = 'Record(s) not found';
        $actual   = $actual['errors'][0];
        $this->assertStringContainsString($expected, $actual);
    }
}
