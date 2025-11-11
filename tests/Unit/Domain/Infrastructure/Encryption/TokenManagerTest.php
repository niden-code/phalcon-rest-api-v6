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

namespace Phalcon\Api\Tests\Unit\Domain\Infrastructure\Encryption;

use Exception;
use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenManager;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenCacheInterface;
use Phalcon\Api\Domain\Infrastructure\Encryption\JWTToken;
use Phalcon\Api\Domain\Infrastructure\Env\EnvManager;
use Phalcon\Encryption\Security\JWT\Token\Token;

use function rand;

final class TokenManagerTest extends AbstractUnitTestCase
{
    public function testGetObjectTokenEmpty(): void
    {
        /** @var EnvManager $env */
        $env = $this->container->get(Container::ENV);
        /** @var TokenCacheInterface $tokenCache */
        $tokenCache = $this->container->get(Container::JWT_TOKEN_CACHE);
        /** @var JWTToken $jwtToken */
        $jwtToken   = $this->container->get(Container::JWT_TOKEN);

        $manager = new TokenManager($tokenCache, $env, $jwtToken);

        $expected = null;
        $actual = $manager->getObject('');
        $this->assertSame($expected, $actual);

        $actual = $manager->getObject(null);
        $this->assertSame($expected, $actual);
    }

    public function testGetObjectWithException(): void
    {
        /** @var EnvManager $env */
        $env = $this->container->get(Container::ENV);
        /** @var TokenCacheInterface $tokenCache */
        $tokenCache = $this->container->get(Container::JWT_TOKEN_CACHE);
        $mockJWTToken = $this
            ->getMockBuilder(JWTToken::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getObject',
                ]
            )
            ->getMock()
        ;
        $mockJWTToken
            ->method('getObject')
            ->willThrowException(new Exception('error'))
        ;

        $manager = new TokenManager($tokenCache, $env, $mockJWTToken);

        $expected = null;
        $actual = $manager->getObject('');
        $this->assertSame($expected, $actual);

        $actual = $manager->getObject(null);
        $this->assertSame($expected, $actual);
    }

    public function testGetObjectSuccess(): void
    {
        /** @var EnvManager $env */
        $env = $this->container->get(Container::ENV);
        /** @var TokenCacheInterface $tokenCache */
        $tokenCache = $this->container->get(Container::JWT_TOKEN_CACHE);
        /** @var JWTToken $jwtToken */
        $jwtToken   = $this->container->get(Container::JWT_TOKEN);
        $userData = $this->getNewUserData();
        $userData['usr_id'] = rand(1, 100);

        $token = $this->getUserToken($userData);

        $manager = new TokenManager($tokenCache, $env, $jwtToken);

        $expected = Token::class;
        $actual = $manager->getObject($token);
        $this->assertInstanceOf($expected, $actual);

    }
}
