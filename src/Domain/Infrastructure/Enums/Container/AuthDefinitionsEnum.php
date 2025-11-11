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

namespace Phalcon\Api\Domain\Infrastructure\Enums\Container;

use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Facades\AuthFacade;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Sanitizers\AuthSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validators\AuthLoginValidator;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validators\AuthTokenValidator;
use Phalcon\Api\Domain\Services\Auth\LoginPostService;
use Phalcon\Api\Domain\Services\Auth\LogoutPostService;
use Phalcon\Api\Domain\Services\Auth\RefreshPostService;

/**
 * @phpstan-import-type TService from Container
 */
enum AuthDefinitionsEnum
{
    case AuthLoginPost;
    case AuthLogoutPost;
    case AuthRefreshPost;
    case AuthFacade;
    case AuthSanitizer;
    case AuthLoginValidator;
    case AuthTokenValidator;

    /**
     * @return TService
     */
    public function definition(): array
    {
        return match ($this) {
            self::AuthLoginPost      => [
                'className' => LoginPostService::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::AUTH_FACADE,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::AUTH_LOGIN_VALIDATOR,
                    ],
                ],
            ],
            self::AuthLogoutPost     => $this->getService(LogoutPostService::class),
            self::AuthRefreshPost    => $this->getService(RefreshPostService::class),
            self::AuthFacade         => [
                'className' => AuthFacade::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::USER_REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::AUTH_SANITIZER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::JWT_TOKEN_MANAGER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::SECURITY,
                    ],
                ],
            ],
            self::AuthSanitizer      => [
                'className' => AuthSanitizer::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::FILTER,
                    ],
                ],
            ],
            self::AuthLoginValidator => [
                'className' => AuthLoginValidator::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::VALIDATION,
                    ],
                ],
            ],
            self::AuthTokenValidator => [
                'className' => AuthTokenValidator::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::JWT_TOKEN_MANAGER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::VALIDATION,
                    ],
                ],
            ],
        };
    }

    /**
     * @return TService
     */
    private function getService(string $className): array
    {
        return [
            'className' => $className,
            'arguments' => [
                [
                    'type' => 'service',
                    'name' => Container::AUTH_FACADE,
                ],
                [
                    'type' => 'service',
                    'name' => Container::AUTH_TOKEN_VALIDATOR,
                ],
            ],
        ];
    }
}
