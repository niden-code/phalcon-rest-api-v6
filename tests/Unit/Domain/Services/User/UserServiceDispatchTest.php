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

use Phalcon\Api\Domain\Components\Cache\Cache;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Domain\Components\Enums\Http\RoutesEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class UserServiceDispatchTest extends AbstractUnitTestCase
{
    public function testDispatchGet(): void
    {
        /** @var EnvManager $env */
        $env = $this->container->getShared(Container::ENV);
        /** @var Cache $cache */
        $cache = $this->container->getShared(Container::CACHE);
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);

        $migration  = new UsersMigration($this->getConnection());
        $dbUser     = $this->getNewUser($migration);
        $userId     = $dbUser['usr_id'];
        $token      = $this->getUserToken($dbUser);
        $domainUser = $userMapper->domain($dbUser);

        /**
         * Store the token in the cache
         */
        $cache->storeTokenInCache($env, $domainUser, $token);

        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'REQUEST_URI'        => RoutesEnum::userGet->endpoint(),
        ];

        $_GET = [
            'id' => $userId,
        ];

        ob_start();
        require_once $env->appPath('public/index.php');
        $response = ob_get_clean();

        $contents = json_decode($response, true);

        restore_error_handler();

        $this->assertArrayHasKey('data', $contents);
        $this->assertArrayHasKey('errors', $contents);

        $data   = $contents['data'];
        $errors = $contents['errors'];

        $expected = [];
        $actual   = $errors;
        $this->assertSame($expected, $actual);

        $user     = $userMapper->domain($dbUser);
        $expected = [$userId => $user->toArray()];
        $actual   = $data;
        $this->assertSame($expected, $actual);
    }
}
