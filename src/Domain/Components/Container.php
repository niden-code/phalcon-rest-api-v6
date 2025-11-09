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

namespace Phalcon\Api\Domain\Components;

use Phalcon\Api\Domain\Components\Constants\Cache as CacheConstants;
use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Enums\Container\AuthDefinitionsEnum;
use Phalcon\Api\Domain\Components\Enums\Container\CommonDefinitionsEnum;
use Phalcon\Api\Domain\Components\Enums\Container\UserDefinitionsEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Domain\Components\Middleware\HealthMiddleware;
use Phalcon\Api\Domain\Components\Middleware\NotFoundMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenClaimsMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenPresenceMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenRevokedMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenStructureMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenUserMiddleware;
use Phalcon\Api\Responder\JsonResponder;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Di\Di;
use Phalcon\Di\Service;
use Phalcon\Filter\FilterFactory;
use Phalcon\Filter\Validation;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Registry;

use function sprintf;

/**
 *
 * @phpstan-type TServiceParameter array{
 *     type: 'parameter',
 *     value: mixed
 * }
 * @phpstan-type TServiceService array{
 *     type: 'service',
 *     name: string
 * }
 * @phpstan-type TServiceArguments array<array-key, TServiceParameter|TServiceService>
 * @phpstan-type TServiceCall array{
 *     method: string,
 *     arguments: TServiceArguments
 * }
 *
 * @phpstan-type TService array{
 *     className: string,
 *     arguments?: TServiceArguments,
 *     calls?: array<array-key, TServiceCall>
 * }
 */
class Container extends Di
{
    /** @var string */
    public const APPLICATION = 'application';
    /** @var string */
    public const CACHE = 'cache';
    /** @var string */
    public const CONNECTION = 'connection';
    /** @var string */
    public const ENV = 'env';
    /** @var string */
    public const EVENTS_MANAGER = 'eventsManager';
    /** @var string */
    public const FILTER = 'filter';
    /** @var string */
    public const JWT_TOKEN = 'jwt.token';
    /** @var string */
    public const JWT_TOKEN_CACHE = 'jwt.token.cache';
    /** @var string */
    public const JWT_TOKEN_MANAGER = 'jwt.token.manager';
    /** @var string */
    public const LOGGER = 'logger';
    /** @var string */
    public const REGISTRY = 'registry';
    /** @var string */
    public const REQUEST = 'request';
    /** @var string */
    public const RESPONSE = 'response';
    /** @var string */
    public const ROUTER = 'router';
    /** @var string */
    public const SECURITY = Security::class;
    /** @var string */
    public const TIME = 'time';
    /** @var string */
    public const VALIDATION = Validation::class;
    /**
     * Middleware
     */
    public const MIDDLEWARE_HEALTH                   = HealthMiddleware::class;
    public const MIDDLEWARE_NOT_FOUND                = NotFoundMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_CLAIMS    = ValidateTokenClaimsMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_PRESENCE  = ValidateTokenPresenceMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_REVOKED   = ValidateTokenRevokedMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_STRUCTURE = ValidateTokenStructureMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_USER      = ValidateTokenUserMiddleware::class;
    /**
     * Facades
     */
    public const AUTH_FACADE        = 'auth.facade';
    public const USER_FACADE        = 'user.facade';
    public const USER_FACADE_UPDATE = 'user.facade.update';
    /**
     * Services
     */
    public const AUTH_LOGIN_POST_SERVICE   = 'service.auth.login.post';
    public const AUTH_LOGOUT_POST_SERVICE  = 'service.auth.logout.post';
    public const AUTH_REFRESH_POST_SERVICE = 'service.auth.refresh.post';
    public const USER_DELETE_SERVICE       = 'service.user.delete';
    public const USER_GET_SERVICE          = 'service.user.get';
    public const USER_POST_SERVICE         = 'service.user.post';
    public const USER_PUT_SERVICE          = 'service.user.put';
    /**
     * Mappers
     */
    public const USER_MAPPER = UserMapper::class;
    /**
     * Responders
     */
    public const RESPONDER_JSON = JsonResponder::class;
    /**
     * Repositories
     */
    public const USER_REPOSITORY = 'user.repository';
    /**
     * Sanitizers
     */
    public const AUTH_SANITIZER = 'auth.sanitizer';
    public const USER_SANITIZER = 'user.sanitizer';
    /**
     * Validators
     */
    public const AUTH_LOGIN_VALIDATOR  = 'auth.validator.login';
    public const AUTH_TOKEN_VALIDATOR  = 'auth.validator.token';
    public const USER_VALIDATOR        = 'user.validator.insert';
    public const USER_VALIDATOR_UPDATE = 'user.validator.update';

    public function __construct()
    {
        $this->services = [
            /**
             * Base services
             */
            self::CACHE             => $this->getServiceCache(),
            self::CONNECTION        => $this->getServiceConnection(),
            self::ENV               => new Service(EnvManager::class, true),
            self::EVENTS_MANAGER    => new Service(CommonDefinitionsEnum::EventsManager->definition(), true),
            self::FILTER            => $this->getServiceFilter(),
            self::JWT_TOKEN         => new Service(CommonDefinitionsEnum::JWTToken->definition(), true),
            self::JWT_TOKEN_CACHE   => new Service(CommonDefinitionsEnum::JWTTokenCache->definition(), true),
            self::JWT_TOKEN_MANAGER => new Service(CommonDefinitionsEnum::JWTTokenManager->definition(), true),
            self::LOGGER            => $this->getServiceLogger(),
            self::REGISTRY          => new Service(Registry::class, true),
            self::REQUEST           => new Service(Request::class, true),
            self::RESPONSE          => new Service(Response::class, true),
            self::ROUTER            => new Service(CommonDefinitionsEnum::Router->definition(), true),

            /**
             * Facades
             */
            self::AUTH_FACADE        => new Service(AuthDefinitionsEnum::AuthFacade->definition()),
            self::USER_FACADE        => new Service(UserDefinitionsEnum::UserFacade->definition()),
            self::USER_FACADE_UPDATE => new Service(UserDefinitionsEnum::UserFacadeUpdate->definition()),

            /**
             * Repositories
             */
            self::USER_REPOSITORY    => new Service(UserDefinitionsEnum::UserRepository->definition()),

            /**
             * Sanitizers
             */
            self::AUTH_SANITIZER => new Service(AuthDefinitionsEnum::AuthSanitizer->definition()),
            self::USER_SANITIZER => new Service(UserDefinitionsEnum::UserSanitizer->definition()),

            /**
             * Validators
             */
            self::AUTH_LOGIN_VALIDATOR  => new Service(AuthDefinitionsEnum::AuthLoginValidator->definition()),
            self::AUTH_TOKEN_VALIDATOR  => new Service(AuthDefinitionsEnum::AuthTokenValidator->definition()),
            self::USER_VALIDATOR        => new Service(UserDefinitionsEnum::UserValidator->definition()),
            self::USER_VALIDATOR_UPDATE => new Service(UserDefinitionsEnum::UserValidatorUpdate->definition()),

            /**
             * Services
             */
            self::AUTH_LOGIN_POST_SERVICE   => new Service(AuthDefinitionsEnum::AuthLoginPost->definition()),
            self::AUTH_LOGOUT_POST_SERVICE  => new Service(AuthDefinitionsEnum::AuthLogoutPost->definition()),
            self::AUTH_REFRESH_POST_SERVICE => new Service(AuthDefinitionsEnum::AuthRefreshPost->definition()),
            self::USER_DELETE_SERVICE       => new Service(UserDefinitionsEnum::UserDelete->definition()),
            self::USER_GET_SERVICE          => new Service(UserDefinitionsEnum::UserGet->definition()),
            self::USER_POST_SERVICE         => new Service(UserDefinitionsEnum::UserPost->definition()),
            self::USER_PUT_SERVICE          => new Service(UserDefinitionsEnum::UserPut->definition()),
        ];

        parent::__construct();
    }

    /**
     * @return Service
     */
    private function getServiceCache(): Service
    {
        return new Service(
            function () {
                /** @var EnvManager $env */
                $env = $this->getShared(self::ENV);

                /** @var string $prefix */
                $prefix = $env->get('CACHE_PREFIX', '-rest-');
                /** @var string $host */
                $host = $env->get('CACHE_HOST', 'localhost');
                /** @var int $lifetime */
                $lifetime = $env->get('CACHE_LIFETIME', CacheConstants::CACHE_LIFETIME_DAY, 'int');
                /** @var int $index */
                $index = $env->get('CACHE_INDEX', 0, 'int');
                /** @var int $port */
                $port = $env->get('CACHE_PORT', 6379, 'int');

                $options = [
                    'host'     => $host,
                    'index'    => $index,
                    'lifetime' => $lifetime,
                    'prefix'   => $prefix,
                    'port'     => $port,
                    'uniqueId' => $prefix,
                ];

                /** @var string $adapter */
                $adapter = $env->get('CACHE_ADAPTER', 'redis');

                $serializerFactory = new SerializerFactory();
                $adapterFactory    = new AdapterFactory($serializerFactory);
                $cacheAdapter      = $adapterFactory->newInstance($adapter, $options);

                return new Cache($cacheAdapter);
            },
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceConnection(): Service
    {
        return new Service(
            function () {
                /** @var EnvManager $env */
                $env = $this->getShared(self::ENV);

                /** @var string $dbName */
                $dbName = $env->get('DB_NAME', 'phalcon');
                /** @var string $host */
                $host = $env->get('DB_HOST', 'rest-db');
                /** @var string $password */
                $password = $env->get('DB_PASS', 'secret');
                /** @var int $port */
                $port = $env->get('DB_PORT', 3306, 'int');
                /** @var string $username */
                $username = $env->get('DB_USER', 'root');
                /** @var string $encoding */
                $encoding = $env->get('DB_CHARSET', 'utf8');
                $queries  = ['SET NAMES utf8mb4'];
                $dsn      = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $host,
                    $port,
                    $dbName,
                    $encoding
                );

                return new Connection(
                    $dsn,
                    $username,
                    $password,
                    [],
                    $queries
                );
            },
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceFilter(): Service
    {
        return new Service(
            function () {
                return (new FilterFactory())->newInstance();
            },
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceLogger(): Service
    {
        return new Service(
            function () {
                /** @var EnvManager $env */
                $env = $this->getShared(self::ENV);

                /** @var string $logName */
                $logName = $env->get('LOG_FILENAME', 'rest-api');
                /** @var string $logPath */
                $logPath = $env->get('LOG_PATH', 'storage/logs/');
                $logFile = $env->appPath($logPath) . '/' . $logName . '.log';

                return new Logger(
                    $logName,
                    [
                        'main' => new Stream($logFile),
                    ]
                );
            }
        );
    }
}
