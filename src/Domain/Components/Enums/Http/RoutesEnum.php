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

namespace Phalcon\Api\Domain\Components\Enums\Http;

use Phalcon\Api\Domain\Components\Container;

use function str_replace;

/**
 * @phpstan-type TMiddleware array<string, 'before'|'finish'>
 */
enum RoutesEnum: int
{
    /**
     * Methods
     */
    public const DELETE = 'delete';
    /**
     * Events
     */
    public const EVENT_BEFORE = 'before';
    public const EVENT_FINISH = 'finish';
    public const GET    = 'get';
    public const POST   = 'post';
    public const PUT    = 'put';

    case authLoginPost   = 11;
    case authLogoutPost  = 12;
    case authRefreshPost = 13;

    case userDelete = 21;
    case userGet    = 22;
    case userPost   = 23;
    case userPut    = 24;

    /**
     * @return string
     */
    public function endpoint(): string
    {
        return $this->prefix() . $this->suffix();
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return match ($this) {
            self::authLoginPost,
            self::authLogoutPost,
            self::authRefreshPost,
            self::userPost   => self::POST,
            self::userDelete => self::DELETE,
            self::userGet    => self::GET,
            self::userPut    => self::PUT,
        };
    }

    /**
     * @return TMiddleware
     */
    public static function middleware(): array
    {
        return [
            Container::MIDDLEWARE_NOT_FOUND                => self::EVENT_BEFORE,
            Container::MIDDLEWARE_HEALTH                   => self::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_PRESENCE  => self::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_STRUCTURE => self::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_USER      => self::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_CLAIMS    => self::EVENT_BEFORE,
            Container::MIDDLEWARE_VALIDATE_TOKEN_REVOKED   => self::EVENT_BEFORE,
        ];
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        $endpoint = match ($this) {
            self::authLoginPost,
            self::authLogoutPost,
            self::authRefreshPost => 'auth',
            self::userDelete,
            self::userGet,
            self::userPost,
            self::userPut         => 'user',
        };

        return '/' . str_replace('-', '/', $endpoint);
    }

    public function service(): string
    {
        return match ($this) {
            self::authLoginPost   => Container::AUTH_LOGIN_POST_SERVICE,
            self::authLogoutPost  => Container::AUTH_LOGOUT_POST_SERVICE,
            self::authRefreshPost => Container::AUTH_REFRESH_POST_SERVICE,
            self::userDelete      => Container::USER_DELETE_SERVICE,
            self::userGet         => Container::USER_GET_SERVICE,
            self::userPost        => Container::USER_POST_SERVICE,
            self::userPut         => Container::USER_PUT_SERVICE,
        };
    }

    /**
     * @return string
     */
    public function suffix(): string
    {
        return match ($this) {
            self::authLoginPost   => '/login',
            self::authLogoutPost  => '/logout',
            self::authRefreshPost => '/refresh',
            self::userDelete,
            self::userGet,
            self::userPost,
            self::userPut         => '',
        };
    }
}
