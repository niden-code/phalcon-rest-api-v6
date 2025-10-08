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

namespace Phalcon\Api\Domain\Services\Providers;

use Phalcon\Api\Domain\Interfaces\RoutesInterface;
use Phalcon\Api\Domain\Services\Container;

use function str_replace;

/**
 * @phpstan-import-type TMiddleware from RoutesInterface
 */
enum RoutesEnum: string implements RoutesInterface
{
    case helloGet = '';
    case userGet  = 'user';

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
            self::helloGet,
            self::userGet => self::GET,
        };
    }

    /**
     * @return TMiddleware
     */
    public static function middleware(): array
    {
        return [
            Container::MIDDLEWARE_NOT_FOUND       => self::EVENT_BEFORE,
            Container::MIDDLEWARE_HEALTH          => self::EVENT_BEFORE,
            Container::MIDDLEWARE_RESPONSE_SENDER => self::EVENT_FINISH,
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
            self::helloGet => Container::HELLO_SERVICE,
            self::userGet  => Container::USER_GET_SERVICE,
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
