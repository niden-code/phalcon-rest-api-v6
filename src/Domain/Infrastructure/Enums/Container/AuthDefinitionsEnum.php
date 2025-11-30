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

use Phalcon\Api\Domain\Application\Auth\Command\AuthCommandFactory;
use Phalcon\Api\Domain\Application\Auth\Facade\AuthFacade;
use Phalcon\Api\Domain\Application\Auth\Handler\AuthLoginPostHandler;
use Phalcon\Api\Domain\Application\Auth\Handler\AuthLogoutPostHandler;
use Phalcon\Api\Domain\Application\Auth\Handler\AuthRefreshPostHandler;
use Phalcon\Api\Domain\Application\Auth\Service\AuthLoginPostService;
use Phalcon\Api\Domain\Application\Auth\Service\AuthLogoutPostService;
use Phalcon\Api\Domain\Application\Auth\Service\AuthRefreshPostService;
use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandBus;
use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Sanitizer\AuthSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validator\AuthLoginValidator;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validator\AuthTokenValidator;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repository\UserRepository;
use Phalcon\Api\Domain\Infrastructure\Encryption\Security;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenManager;
use Phalcon\Filter\Validation;

/**
 * @phpstan-import-type TService from Container
 */
enum AuthDefinitionsEnum: string implements DefinitionsEnumInterface
{
    case AuthCommandFactory = AuthCommandFactory::class;
    case AuthLoginPost      = AuthLoginPostService::class;
    case AuthLogoutPost     = AuthLogoutPostService::class;
    case AuthRefreshPost    = AuthRefreshPostService::class;
    case AuthFacade         = AuthFacade::class;
    case AuthLoginHandler   = AuthLoginPostHandler::class;
    case AuthLogoutHandler  = AuthLogoutPostHandler::class;
    case AuthRefreshHandler = AuthRefreshPostHandler::class;
    case AuthSanitizer      = AuthSanitizer::class;
    case AuthLoginValidator = AuthLoginValidator::class;
    case AuthTokenValidator = AuthTokenValidator::class;

    /**
     * @return TService
     */
    public function definition(): array
    {
        return match ($this) {
            self::AuthCommandFactory => [
                'className' => AuthCommandFactory::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => AuthSanitizer::class,
                    ],
                ],
            ],
            self::AuthLoginPost      => $this->getService(AuthLoginPostService::class),
            self::AuthLogoutPost     => $this->getService(AuthLogoutPostService::class),
            self::AuthRefreshPost    => $this->getService(AuthRefreshPostService::class),
            self::AuthFacade         => [
                'className' => AuthFacade::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => CommandBus::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => AuthCommandFactory::class,
                    ],
                ],
            ],
            self::AuthLoginHandler   => [
                'className' => AuthLoginPostHandler::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => UserRepository::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => TokenManager::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => Security::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => AuthLoginValidator::class,
                    ],
                ],
            ],
            self::AuthLogoutHandler  => $this->getHandlerService(AuthLogoutPostHandler::class),
            self::AuthRefreshHandler => $this->getHandlerService(AuthRefreshPostHandler::class),
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
                        'name' => Validation::class,
                    ],
                ],
            ],
            self::AuthTokenValidator => [
                'className' => AuthTokenValidator::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => TokenManager::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => UserRepository::class,
                    ],
                    [
                        'type' => 'service',
                        'name' => Validation::class,
                    ],
                ],
            ],
        };
    }

    public function isShared(): bool
    {
        return match ($this) {
            self::AuthSanitizer => true,
            default => false,
        };
    }

    /**
     * @param class-string $className
     *
     * @return TService
     */
    private function getHandlerService(string $className): array
    {
        return [
            'className' => $className,
            'arguments' => [
                [
                    'type' => 'service',
                    'name' => TokenManager::class,
                ],
                [
                    'type' => 'service',
                    'name' => AuthTokenValidator::class,
                ],
            ],
        ];
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
                    'name' => AuthFacade::class,
                ],
            ],
        ];
    }
}
