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

namespace Phalcon\Api\Domain\Components\Enums\Container;

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Encryption\TokenCache;
use Phalcon\Api\Domain\Components\Encryption\TokenManager;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Router;

/**
 * @phpstan-import-type TService from Container
 */
enum CommonDefinitionsEnum
{
    case EventsManager;
    case JWTToken;
    case JWTTokenCache;
    case JWTTokenManager;
    case Router;

    /**
     * @return TService
     */
    public function definition(): array
    {
        return match ($this) {
            self::EventsManager        => [
                'className' => EventsManager::class,
                'calls'     => [
                    [
                        'method' => 'enablePriorities',
                        'arguments' => [
                            [
                                'type' => 'parameter',
                                'value' => true,
                            ]
                        ]
                    ]
                ]
            ],
            self::JWTToken        => [
                'className' => JWTToken::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::ENV,
                    ],
                ],
            ],
            self::JWTTokenCache   => [
                'className' => TokenCache::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::CACHE,
                    ],
                ],
            ],
            self::JWTTokenManager => [
                'className' => TokenManager::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::JWT_TOKEN_CACHE,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::ENV,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::JWT_TOKEN,
                    ],
                ],
            ],
            self::Router          => [
                'className' => Router::class,
                'arguments' => [
                    [
                        'type'  => 'parameter',
                        'value' => false,
                    ],
                ],
            ]
        };
    }
}
