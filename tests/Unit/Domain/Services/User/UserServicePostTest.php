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
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Services\User\UserPostService;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Filter\Filter;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class UserServicePostTest extends AbstractUnitTestCase
{
    public function testServiceFailureNoIdReturned(): void
    {
        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'insert',
                ]
            )
            ->getMock()
        ;
        $userRepository
            ->method('insert')
            ->willReturn(0)
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

        /**
         * Difference of achieving the same thing - see test above on
         * how the class is used without getting it from the DI container
         */
        $this->container->setShared(Container::REPOSITORY, $repository);

        /** @var UserPostService $service */
        $service = $this->container->get(Container::USER_POST_SERVICE);
        /** @var TransportRepository $service */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $userData = $this->getNewUserData();

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $transport->newUser($userData);
        $domainData = $domainUser->toArray();
        $domainData = $domainData[0];

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::ERROR;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = ['Cannot create database record: No id returned'];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceFailurePdoError(): void
    {
        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'insert',
                ]
            )
            ->getMock()
        ;
        $userRepository
            ->method('insert')
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

        /** @var Filter $filter */
        $filter = $this->container->get(Container::FILTER);
        /** @var Security $security */
        $security = $this->container->get(Container::SECURITY);
        /** @var TransportRepository $transport */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $service = new UserPostService($repository, $transport, $filter, $security);

        $userData = $this->getNewUserData();

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $transport->newUser($userData);
        $domainData = $domainUser->toArray();
        $domainData = $domainData[0];

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::ERROR;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('errors', $actual);

        $errors = $actual['errors'];

        $expected = ['Cannot create database record: abcde'];
        $actual   = $errors;
        $this->assertSame($expected, $actual);
    }

    public function testServiceFailureValidation(): void
    {
        /** @var UserPostService $service */
        $service = $this->container->get(Container::USER_POST_SERVICE);
        /** @var TransportRepository $transport */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $userData = $this->getNewUserData();

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
        $domainUser = $transport->newUser($userData);
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
        /** @var UserPostService $service */
        $service = $this->container->get(Container::USER_POST_SERVICE);
        /** @var TransportRepository $transport */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $userData                       = $this->getNewUserData();
        $userData['usr_created_usr_id'] = 4;
        $userData['usr_updated_usr_id'] = 5;

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $transport->newUser($userData);
        $domainData = $domainUser->toArray();
        $domainData = $domainData[0];

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::CREATED;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $data = $actual['data'];

        $userId = array_key_first($data);

        $this->assertGreaterThan(0, $userId);

        $data = $data[$userId];

        $expected = $domainData['status'];
        $actual   = $data['status'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['email'];
        $actual   = $data['email'];
        $this->assertSame($expected, $actual);

        $actual = str_starts_with($data['password'], '$argon2i$');
        $this->assertTrue($actual);

        $expected = $domainData['namePrefix'];
        $actual   = $data['namePrefix'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameFirst'];
        $actual   = $data['nameFirst'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameMiddle'];
        $actual   = $data['nameMiddle'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameLast'];
        $actual   = $data['nameLast'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameSuffix'];
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

        $expected = $domainData['preferences'];
        $actual   = $data['preferences'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['createdDate'];
        $actual   = $data['createdDate'];
        $this->assertSame($expected, $actual);

        $expected = 4;
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
        /** @var UserPostService $service */
        $service = $this->container->get(Container::USER_POST_SERVICE);
        /** @var TransportRepository $transport */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $userData = $this->getNewUserData();
        unset(
            $userData['usr_created_date'],
            $userData['usr_updated_date'],
        );

        /**
         * $userData is a db record. We need a domain object here
         */
        $domainUser = $transport->newUser($userData);
        $domainData = $domainUser->toArray();
        $domainData = $domainData[0];

        $payload = $service->__invoke($domainData);

        $expected = DomainStatus::CREATED;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $data = $actual['data'];

        $userId = array_key_first($data);

        $this->assertGreaterThan(0, $userId);

        $data = $data[$userId];

        $expected = $domainData['status'];
        $actual   = $data['status'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['email'];
        $actual   = $data['email'];
        $this->assertSame($expected, $actual);

        $actual = str_starts_with($data['password'], '$argon2i$');
        $this->assertTrue($actual);

        $expected = $domainData['namePrefix'];
        $actual   = $data['namePrefix'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameFirst'];
        $actual   = $data['nameFirst'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameMiddle'];
        $actual   = $data['nameMiddle'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameLast'];
        $actual   = $data['nameLast'];
        $this->assertSame($expected, $actual);

        $expected = $domainData['nameSuffix'];
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

        $expected = $domainData['preferences'];
        $actual   = $data['preferences'];
        $this->assertSame($expected, $actual);

        $actual = $data['createdDate'];
        $this->assertStringContainsString($today, $actual);

        $expected = 0;
        $actual   = $data['createdUserId'];
        $this->assertSame($expected, $actual);

        $actual = $data['updatedDate'];
        $this->assertStringContainsString($today, $actual);

        $expected = 0;
        $actual   = $data['updatedUserId'];
        $this->assertSame($expected, $actual);
    }
}
