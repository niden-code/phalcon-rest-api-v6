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

namespace Phalcon\Api\Tests\Unit\Domain\Components\Encryption;

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Exceptions\TokenValidationException;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Encryption\Security\JWT\Token\Token;

use function sleep;

final class JWTTokenTest extends AbstractUnitTestCase
{
    private JWTToken $jwtToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->jwtToken = $this->container->get(Container::JWT_TOKEN);
    }

    public function testGetForUserReturnsTokenString(): void
    {
        $user = $this->getUserData();

        $token = $this->jwtToken->getForUser($user);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testGetObjectReturnsPlainToken(): void
    {
        $user = $this->getUserData();

        $tokenString = $this->jwtToken->getForUser($user);

        $plain = $this->jwtToken->getObject($tokenString);
        $this->assertInstanceOf(Token::class, $plain);
    }

    public function testGetObjectThrowsOnCannotDecodeContent(): void
    {
        $this->expectException(TokenValidationException::class);
        $this->expectExceptionMessage('Invalid Header (not an array)');

        // Simulate exception by calling with invalid token
        $this->jwtToken->getObject('invalid.token.content');
    }

    public function testGetObjectThrowsOnInvalidTokenStructure(): void
    {
        $this->expectException(TokenValidationException::class);

        // This will throw, as the structure is invalid
        $this->jwtToken->getObject('abc.def.ghi');
    }

    public function testGetUserReturnsUserArray(): void
    {
        $user = $this->getUserData();

        $tokenString = $this->jwtToken->getForUser($user);
        $plain       = $this->jwtToken->getObject($tokenString);

        $userRepository = $this
            ->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'findOneBy',
                ]
            )
            ->getMock()
        ;

        $userRepository->expects($this->once())
                       ->method('findOneBy')
                       ->willReturn($user)
        ;

        $mockRepository = $this
            ->getMockBuilder(QueryRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'user',
                ]
            )
            ->getMock()
        ;
        $mockRepository->expects($this->once())
                       ->method('user')
                       ->willReturn($userRepository)
        ;

        $result = $this->jwtToken->getUser($mockRepository, $plain);
        $this->assertEquals($user, $result);
    }

    public function testValidateSuccess(): void
    {
        $user = $this->getUserData();

        $tokenString   = $this->jwtToken->getForUser($user);
        $plain         = $this->jwtToken->getObject($tokenString);
        $userTransport = new UserTransport($user);

        sleep(1);

        $actual = $this->jwtToken->validate($plain, $userTransport);

        $this->assertSame([], $actual);
    }

    /**
     * @return array
     */
    private function getUserData(): array
    {
        $user                       = $this->getNewUserData();
        $user['usr_id']             = 2;
        $user['usr_token_id']       = $this->getStrongPassword();
        $user['usr_issuer']         = 'issuer';
        $user['usr_token_password'] = $this->getStrongPassword();

        return $user;
    }
}
