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

namespace Phalcon\Api\Tests\Unit\Domain\Infrastructure\Enums\Http;

use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\Enums\Http\RoutesEnum;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RoutesEnumTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                RoutesEnum::authLoginPost,
                '/auth',
                '/login',
                '/auth/login',
                RoutesEnum::POST,
                Container::AUTH_LOGIN_POST_SERVICE,
            ],
            [
                RoutesEnum::authLogoutPost,
                '/auth',
                '/logout',
                '/auth/logout',
                RoutesEnum::POST,
                Container::AUTH_LOGOUT_POST_SERVICE,
            ],
            [
                RoutesEnum::authRefreshPost,
                '/auth',
                '/refresh',
                '/auth/refresh',
                RoutesEnum::POST,
                Container::AUTH_REFRESH_POST_SERVICE,
            ],
            [
                RoutesEnum::userDelete,
                '/user',
                '',
                '/user',
                RoutesEnum::DELETE,
                Container::USER_DELETE_SERVICE,
            ],
            [
                RoutesEnum::userGet,
                '/user',
                '',
                '/user',
                RoutesEnum::GET,
                Container::USER_GET_SERVICE,
            ],
            [
                RoutesEnum::userPost,
                '/user',
                '',
                '/user',
                RoutesEnum::POST,
                Container::USER_POST_SERVICE,
            ],
            [
                RoutesEnum::userPut,
                '/user',
                '',
                '/user',
                RoutesEnum::PUT,
                Container::USER_PUT_SERVICE,
            ],
        ];
    }

    public function testCheckCount(): void
    {
        $expected = 7;
        $actual   = RoutesEnum::cases();
        $this->assertCount($expected, $actual);
    }

    #[DataProvider('getExamples')]
    public function testCheckItems(
        RoutesEnum $element,
        string $prefix,
        string $suffix,
        string $endpoint,
        string $method,
        string $service
    ) {
        $expected = $prefix;
        $actual   = $element->prefix();
        $this->assertSame($expected, $actual);

        $expected = $suffix;
        $actual   = $element->suffix();
        $this->assertSame($expected, $actual);

        $expected = $endpoint;
        $actual   = $element->endpoint();
        $this->assertSame($expected, $actual);

        $expected = $method;
        $actual   = $element->method();
        $this->assertSame($expected, $actual);

        $expected = $service;
        $actual   = $element->service();
        $this->assertSame($expected, $actual);
    }

    public function testMiddleware(): void
    {
        $expected = [
            Container::MIDDLEWARE_NOT_FOUND                => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_HEALTH                   => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_PRESENCE  => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_STRUCTURE => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_USER      => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_CLAIMS    => RoutesEnum::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_REVOKED   => RoutesEnum::EVENT_BEFORE,
        ];
        $actual   = RoutesEnum::middleware();
        $this->assertSame($expected, $actual);
    }
}
