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

namespace Phalcon\Api\Tests\Unit\Domain\User;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\User\UserGetService;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\DataMapper\Pdo\Connection;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class UserServiceTest extends AbstractUnitTestCase
{
//    public function testDispatch(): void
//    {
//        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
//        $_SERVER = [
//            'REQUEST_METHOD'     => 'GET',
//            'REQUEST_TIME_FLOAT' => $time,
//            'REQUEST_URI'        => '/',
//        ];
//
//        ob_start();
//        require_once EnvManager::appPath('public/index.php');
//        $response = ob_get_clean();
//
//        $contents = json_decode($response, true);
//
//        restore_error_handler();
//
//        $this->assertArrayHasKey('data', $contents);
//        $this->assertArrayHasKey('errors', $contents);
//
//        $data   = $contents['data'];
//        $errors = $contents['errors'];
//
//        $expected = [];
//        $actual   = $errors;
//        $this->assertSame($expected, $actual);
//
//        $expected = 'Hello World!!! - ';
//        $actual   = $data[0];
//        $this->assertStringContainsString($expected, $actual);
//    }
//
    public function testServiceEmptyUserId(): void
    {
        $container = new Container();
        /** @var UserGetService $service */
        $service = $container->get(Container::USER_GET_SERVICE);

        $payload = $service->__invoke([]);

        $expected = DomainStatus::NOT_FOUND;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('results', $actual);

        $expected = 'Record(s) not found';
        $actual   = $actual['results'][0];
        $this->assertStringContainsString($expected, $actual);
    }

    public function testServiceWrongUserId(): void
    {
        $container = new Container();
        /** @var UserGetService $service */
        $service = $container->get(Container::USER_GET_SERVICE);

        $payload = $service->__invoke(
            [
                'userId' => 999999
            ]
        );

        $expected = DomainStatus::NOT_FOUND;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('results', $actual);

        $expected = 'Record(s) not found';
        $actual   = $actual['results'][0];
        $this->assertStringContainsString($expected, $actual);
    }

    public function testServiceWithUserId(): void
    {
        $container = new Container();
        /** @var Connection $connection */
        $connection = $container->getShared(Container::CONNECTION);
        /** @var UserGetService $service */
        $service = $container->get(Container::USER_GET_SERVICE);

        $migration = new UsersMigration($connection);
        $this->setConnection($connection);
        $dbUser = $this->getNewUser($migration);
        $userId = $dbUser['usr_id'];

        $payload = $service->__invoke(
            [
                'userId' => $userId
            ]
        );

        $expected = DomainStatus::SUCCESS;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('results', $actual);

        $user = $actual['results'];
        $key  = array_key_first($user);
        $user = $user[$key];

        $expected = $dbUser['usr_id'];
        $actual   = $user['id'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_status_flag'];
        $actual   = $user['status'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_username'];
        $actual   = $user['username'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_password'];
        $actual   = $user['password'];
        $this->assertSame($expected, $actual);
    }
}
