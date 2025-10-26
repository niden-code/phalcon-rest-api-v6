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
enum RoutesEnum: string
{
    public const DELETE       = 'delete';
    public const EVENT_BEFORE = 'before';
    public const EVENT_FINISH = 'finish';
    public const GET          = 'get';
    public const POST         = 'post';
    public const PUT          = 'put';

    case authLoginPost = 'auth/login';
    case userGet       = 'user';

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
            self::authLoginPost => self::POST,
            self::userGet       => self::GET,
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
        ];
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        return '/' . str_replace('-', '/', $this->value);
    }

    public function service(): string
    {
        return match ($this) {
            self::authLoginPost => Container::AUTH_LOGIN_POST_SERVICE,
            self::userGet       => Container::USER_GET_SERVICE,
        };
    }

    /**
     * @return string
     */
    public function suffix(): string
    {
        return '';
    }
}
