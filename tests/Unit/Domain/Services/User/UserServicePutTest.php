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

use DateTimeImmutable;
use PayloadInterop\DomainStatus;
use PDOException;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Services\User\UserPutService;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use PHPUnit\Framework\Attributes\BackupGlobals;

use function array_shift;

#[BackupGlobals(true)]
final class UserServicePutTest extends AbstractUnitTestCase
{
    public function testServiceFailureRecordNotFound(): void
    {
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $userData = $this->getNewUserData();

        $userData['usr_id'] = 1;

        $findByUser = $userMapper->domain($userData);

        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'findById'
                ]
            )
            ->getMock()
        ;
        $userRepository->method('findById')->willReturn(null);

        $repository = $this
            ->getMockBuilder(QueryRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'user',
                ]
            )
            ->getMock()
        ;
        $repository->method('user')->willReturn($userRepository);

        $this->container->setShared(Container::REPOSITORY, $repository);

        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);

        /**
         * Update user
         */
        $userData           = $this->getNewUserData();
        $userData['usr_id'] = 1;

        $updateUser = $userMapper->domain($userData);
        $updateUser = $updateUser->toArray();
        $updateUser = array_shift($updateUser);

        $payload = $service->__invoke($updateUser);

        $expected = DomainStatus::NOT_FOUND;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = ['Record(s) not found'];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceFailureNoIdReturned(): void
    {
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $userData = $this->getNewUserData();

        $userData['usr_id'] = 1;

        $findByUser = $userMapper->domain($userData);

        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'update',
                    'findById'
                ]
            )
            ->getMock()
        ;
        $userRepository->method('update')->willReturn(0);
        $userRepository->method('findById')->willReturn($findByUser);

        $repository = $this
            ->getMockBuilder(QueryRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'user',
                ]
            )
            ->getMock()
        ;
        $repository->method('user')->willReturn($userRepository);

        $this->container->setShared(Container::REPOSITORY, $repository);

        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);

        /**
         * Update user
         */
        $userData           = $this->getNewUserData();
        $userData['usr_id'] = 1;

        $updateUser = $userMapper->domain($userData);
        $updateUser = $updateUser->toArray();
        $updateUser = array_shift($updateUser);

        $payload = $service->__invoke($updateUser);

        $expected = DomainStatus::ERROR;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = ['Cannot update database record: No id returned'];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceFailurePdoError(): void
    {
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $userData = $this->getNewUserData();

        $userData['usr_id'] = 1;

        $findByUser = $userMapper->domain($userData);

        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'update',
                    'findById'
                ]
            )
            ->getMock()
        ;
        $userRepository->method('findById')->willReturn($findByUser);
        $userRepository
            ->method('update')
            ->willThrowException(new PDOException('abcde'))
        ;

        $repository = $this
            ->getMockBuilder(QueryRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'user',
                ]
            )
            ->getMock()
        ;
        $repository
            ->method('user')
            ->willReturn($userRepository)
        ;

        $this->container->set(Container::REPOSITORY, $repository);
        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);

        $userData           = $this->getNewUserData();
        $userData['usr_id'] = 1;

        /**
         * $userData is a db record. We need a domain object here
         */
        $updateUser = $userMapper->domain($userData);
        $updateUser = $updateUser->toArray();
        $updateUser = array_shift($updateUser);

        $payload = $service->__invoke($updateUser);

        $expected = DomainStatus::ERROR;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = ['Cannot update database record: abcde'];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceFailureValidation(): void
    {
        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $userData   = $this->getNewUserData();

        unset(
            $userData['usr_email'],
            $userData['usr_password'],
            $userData['usr_issuer'],
            $userData['usr_token_password'],
            $userData['usr_token_id']
        );

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $userMapper->domain($userData);
        $domainData = $domainUser->toArray();
        $domainData = $domainData[0];

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::INVALID;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = [
            ['Field email cannot be empty.'],
            ['Field password cannot be empty.'],
            ['Field issuer cannot be empty.'],
            ['Field tokenPassword cannot be empty.'],
            ['Field tokenId cannot be empty.'],
        ];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceSuccess(): void
    {
        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);

        $migration          = new UsersMigration($this->getConnection());
        $dbUser             = $this->getNewUser($migration);
        $userId             = $dbUser['usr_id'];
        $userData           = $this->getNewUserData();
        $userData['usr_id'] = $userId;
        /**
         * Don't hash the password
         */
        $userData['usr_password'] = $this->getStrongPassword();

        $userData['usr_created_usr_id'] = 4;
        $userData['usr_updated_usr_id'] = 5;

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $userMapper->domain($userData);
        $domainData = $domainUser->toArray();
        $domainData = array_shift($domainData);

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::UPDATED;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $data = $actual['data'];

        $this->assertArrayHasKey($userId, $data);

        $data = $data[$userId];

        $expected = $domainData['status'];
        $actual   = $data['status'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['email'];
        $actual   = $data['email'];
        $this->assertSame($expected, $actual);

        $actual = str_starts_with($data['password'], '$argon2i$');
        $this->assertTrue($actual);

        $expected = htmlspecialchars($domainData['namePrefix']);
        $actual   = $data['namePrefix'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameFirst']);
        $actual   = $data['nameFirst'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameMiddle']);
        $actual   = $data['nameMiddle'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameLast']);
        $actual   = $data['nameLast'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameSuffix']);
        $actual   = $data['nameSuffix'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['issuer'];
        $actual   = $data['issuer'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['tokenPassword'];
        $actual   = $data['tokenPassword'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['tokenId'];
        $actual   = $data['tokenId'];
        $this->assertSame($expected, $actual);

        $expected = '';
        $actual   = $data['preferences'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_date'];
        $actual   = $data['createdDate'];
        $this->assertSame($expected, $actual);

        $expected = 0;
        $actual   = $data['createdUserId'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['updatedDate'];
        $actual   = $data['updatedDate'];
        $this->assertSame($expected, $actual);

        $expected = 5;
        $actual   = $data['updatedUserId'];
        $this->assertSame($expected, $actual);
    }

    public function testServiceSuccessEmptyDates(): void
    {
        $now   = new DateTimeImmutable();
        $today = $now->format('Y-m-d');
        /** @var UserPutService $service */
        $service = $this->container->get(Container::USER_PUT_SERVICE);
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);

        $migration = new UsersMigration($this->getConnection());
        $dbUser    = $this->getNewUser($migration);

        $userId             = $dbUser['usr_id'];
        $userData           = $this->getNewUserData();
        $userData['usr_id'] = $userId;
        /**
         * Don't hash the password
         */
        $userData['usr_password'] = $this->getStrongPassword();

        unset(
            $userData['usr_updated_date'],
        );

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $userMapper->domain($userData);
        $domainData = $domainUser->toArray();
        $domainData = array_shift($domainData);

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::UPDATED;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $data = $actual['data'];

        $this->assertArrayHasKey($userId, $data);

        $data = $data[$userId];

        $expected = $domainData['status'];
        $actual   = $data['status'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['email'];
        $actual   = $data['email'];
        $this->assertSame($expected, $actual);

        $actual = str_starts_with($data['password'], '$argon2i$');
        $this->assertTrue($actual);

        $expected = htmlspecialchars($domainData['namePrefix']);
        $actual   = $data['namePrefix'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameFirst']);
        $actual   = $data['nameFirst'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameMiddle']);
        $actual   = $data['nameMiddle'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameLast']);
        $actual   = $data['nameLast'];
        $this->assertSame($expected, $actual);

        $expected = htmlspecialchars($domainData['nameSuffix']);
        $actual   = $data['nameSuffix'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['issuer'];
        $actual   = $data['issuer'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['tokenPassword'];
        $actual   = $data['tokenPassword'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['tokenId'];
        $actual   = $data['tokenId'];
        $this->assertSame($expected, $actual);

        $expected = '';
        $actual   = $data['preferences'];
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_date'];
        $actual   = $data['createdDate'];
        $this->assertSame($expected, $actual);

        $expected = 0;
        $actual   = $data['createdUserId'];
        $this->assertSame($expected, $actual);

        $today    = date('Y-m-d ');
        $actual   = $data['updatedDate'];
        $this->assertStringContainsString($today, $actual);

        $expected = $dbUser['usr_updated_usr_id'];
        $actual   = $data['updatedUserId'];
        $this->assertSame($expected, $actual);
    }
}
