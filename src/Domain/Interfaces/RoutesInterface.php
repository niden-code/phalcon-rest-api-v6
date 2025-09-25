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

namespace Phalcon\Api\Domain\Interfaces;

use BackedEnum;

/**
 * Interface for route enumerations
 *
 * @phpstan-type TMiddleware array<string, 'before'|'finish'>
 */
interface RoutesInterface extends BackedEnum
{
    public const DELETE = 'delete';
    public const EVENT_BEFORE = 'before';
    public const EVENT_FINISH = 'finish';
    public const GET    = 'get';
    public const POST   = 'post';
    public const PUT    = 'put';


    /**
     * @return string
     */
    public function endpoint(): string;

    /**
     * @return string
     */
    public function method(): string;

    /**
     * @return TMiddleware
     */
    public static function middleware(): array;

    /**
     * @return string
     */
    public function prefix(): string;

    /**
     * @return string
     */
    public function service(): string;

    /**
     * @return string
     */
    public function suffix(): string;
}
