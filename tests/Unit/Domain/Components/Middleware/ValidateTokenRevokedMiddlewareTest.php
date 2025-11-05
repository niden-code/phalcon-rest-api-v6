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

namespace Phalcon\Api\Tests\Unit\Domain\Components\Middleware;

use Phalcon\Api\Domain\Components\Cache\Cache;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\Mvc\Micro;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class ValidateTokenRevokedMiddlewareTest extends AbstractUnitTestCase
{
    public function testValidateTokenRevokedFailureInvalidToken(): void
    {
        $migration = new UsersMigration($this->getConnection());
        $user      = $this->getNewUser($migration);
        $tokenUser = $user;

        [$micro, $middleware] = $this->setupTest();

        $token = $this->getUserToken($tokenUser);

        /**
         * Store the user in the session
         */
        /** @var UserTransport $userRepository */
        $userRepository = $micro->getSharedService(Container::REPOSITORY_TRANSPORT);
        $userRepository->setSessionUser($user);

        // There is no entry in the cache for this token, so this should fail.
        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'REQUEST_URI'        => '/user?id=1234',
        ];

        ob_start();
        $middleware->call($micro);
        $contents = ob_get_clean();

        $contents = json_decode($contents, true);

        $data   = $contents['data'];
        $errors = $contents['errors'];

        $this->assertSame([], $data);

        $expected = [HttpCodesEnum::AppTokenNotValid->error()];
        $this->assertSame($expected, $errors);
    }

    public function testValidateTokenRevokedSuccess(): void
    {
        $migration = new UsersMigration($this->getConnection());
        $user      = $this->getNewUser($migration);
        $tokenUser = $user;

        [$micro, $middleware] = $this->setupTest();

        $token = $this->getUserToken($tokenUser);

        /**
         * Store the user in the session
         */
        /** @var UserTransport $userRepository */
        $userRepository = $micro->getSharedService(Container::REPOSITORY_TRANSPORT);
        $userRepository->setSessionUser($user);
        /** @var Cache $cache */
        $cache       = $micro->getSharedService(Container::CACHE);
        $sessionUser = $userRepository->getSessionUser();
        $cacheKey    = $cache->getCacheTokenKey($sessionUser, $token);
        $payload     = [
            'token' => $token,
        ];
        $actual      = $cache->set($cacheKey, $payload, 2);
        $this->assertTrue($actual);

        // There is no entry in the cache for this token, so this should fail.
        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'REQUEST_URI'        => '/user?id=1234',
        ];

        $contents = $middleware->call($micro);

        $this->assertTrue($contents);
    }

    /**
     * @return array
     */
    private function setupTest(): array
    {
        $micro      = new Micro($this->container);
        $middleware = $this->container->get(Container::MIDDLEWARE_VALIDATE_TOKEN_REVOKED);

        return [$micro, $middleware];
    }
}
