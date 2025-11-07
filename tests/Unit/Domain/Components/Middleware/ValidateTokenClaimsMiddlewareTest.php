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

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\Mvc\Micro;
use Phalcon\Support\Registry;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_replace;

#[BackupGlobals(true)]
final class ValidateTokenClaimsMiddlewareTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                ['usr_token_id' => 'abcdef'],
                [
                    'Validation: incorrect Id',
                ],
            ],
            [
                ['usr_issuer' => 'abcdef'],
                [
                    'Validation: incorrect issuer',
                ],
            ],
            [
                ['algo' => 'sha256'],
                [
                    'Validation: the signature does not match',
                ],
            ],
            [
                ['audience' => 'wrong audience'],
                [
                    'Validation: audience not allowed',
                ],
            ],
            [
                ['usr_id' => 1],
                [
                    'Validation: incorrect uid',
                ],
            ],
        ];
    }

    #[DataProvider('getExamples')]
    public function testValidateTokenClaimsFailure(
        array $userData,
        array $expectedErrors
    ): void {
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $migration = new UsersMigration($this->getConnection());
        $user      = $this->getNewUser($migration);

        [$micro, $middleware, $jwtToken] = $this->setupTest();

        /**
         * Make the signature non valid
         */
        $tokenUser   = array_replace($user, $userData);
        $token       = $this->getUserToken($tokenUser);
        $tokenObject = $jwtToken->getObject($token);
        $domainUser  = $userMapper->domain($user);

        /**
         * Store the user in the registry
         */
        /** @var Registry $registry */
        $registry = $this->container->get(Container::REGISTRY);
        $registry->set('user', $domainUser);
        $registry->set('token', $tokenObject);

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

        $expected = [$expectedErrors];
        $this->assertSame($expected, $errors);
    }

    public function testValidateTokenClaimsSuccess(): void
    {
        /** @var UserMapper $userMapper */
        $userMapper = $this->container->get(Container::USER_MAPPER);
        $migration = new UsersMigration($this->getConnection());
        $user      = $this->getNewUser($migration);
        $tokenUser = $userMapper->domain($user);

        [$micro, $middleware, $jwtToken] = $this->setupTest();

        $token       = $this->getUserToken($user);
        $tokenObject = $jwtToken->getObject($token);

        /**
         * Store the user in the registry
         */
        /** @var Registry $registry */
        $registry = $this->container->get(Container::REGISTRY);
        $registry->set('user', $tokenUser);
        $registry->set('token', $tokenObject);

        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'REQUEST_URI'        => '/user?id=1234',
        ];

        ob_start();
        $actual = $middleware->call($micro);
        ob_get_clean();

        $this->assertTrue($actual);
    }

    /**
     * @return array
     */
    private function setupTest(): array
    {
        $micro      = new Micro($this->container);
        $middleware = $this->container->get(Container::MIDDLEWARE_VALIDATE_TOKEN_CLAIMS);
        $jwtToken   = $this->container->getShared(Container::JWT_TOKEN);

        return [$micro, $middleware, $jwtToken];
    }
}
