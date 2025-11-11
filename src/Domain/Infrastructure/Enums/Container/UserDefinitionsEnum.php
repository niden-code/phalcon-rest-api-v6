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
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Facades\UserFacade;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repositories\UserRepository;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Sanitizers\UserSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Validators\UserValidator;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Validators\UserValidatorUpdate;
use Phalcon\Api\Domain\Services\User\UserDeleteService;
use Phalcon\Api\Domain\Services\User\UserGetService;
use Phalcon\Api\Domain\Services\User\UserPostService;
use Phalcon\Api\Domain\Services\User\UserPutService;

/**
 * @phpstan-import-type TService from Container
 */
enum UserDefinitionsEnum
{
    case UserDelete;
    case UserGet;
    case UserPost;
    case UserPut;
    case UserFacade;
    case UserFacadeUpdate;
    case UserRepository;
    case UserSanitizer;
    case UserValidator;
    case UserValidatorUpdate;

    /**
     * @return TService
     */
    public function definition(): array
    {
        return match ($this) {
            self::UserDelete          => $this->getService(UserDeleteService::class),
            self::UserGet             => $this->getService(UserGetService::class),
            self::UserPost            => $this->getService(UserPostService::class),
            self::UserPut             => [
                'className' => UserPutService::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::USER_FACADE_UPDATE,
                    ],
                ],
            ],
            self::UserFacade          => [
                'className' => UserFacade::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::USER_SANITIZER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_VALIDATOR,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_MAPPER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::SECURITY,
                    ],
                ],
            ],
            self::UserFacadeUpdate    => [
                'className' => UserFacade::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::USER_SANITIZER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_VALIDATOR_UPDATE,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_MAPPER,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::SECURITY,
                    ],
                ],
            ],
            self::UserRepository      => [
                'className' => UserRepository::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::CONNECTION,
                    ],
                    [
                        'type' => 'service',
                        'name' => Container::USER_MAPPER,
                    ],
                ],
            ],
            self::UserSanitizer       => [
                'className' => UserSanitizer::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => Container::FILTER,
                    ],
                ],
            ],
            self::UserValidator       => $this->getServiceValidator(UserValidator::class),
            self::UserValidatorUpdate => $this->getServiceValidator(UserValidatorUpdate::class),
        };
    }

    /**
     * @param class-string $className
     *
     * @return TService
     */
    private function getService(string $className): array
    {
        return [
            'className' => $className,
            'arguments' => [
                [
                    'type' => 'service',
                    'name' => Container::USER_FACADE,
                ],
            ],
        ];
    }

    /**
     * @param class-string $className
     *
     * @return TService
     */
    private function getServiceValidator(string $className): array
    {
        return [
            'className' => $className,
            'arguments' => [
                [
                    'type' => 'service',
                    'name' => Container::VALIDATION,
                ],
            ],
        ];
    }
}
