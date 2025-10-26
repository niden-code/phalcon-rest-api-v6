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

use Phalcon\Api\Domain\Components\Cache\Cache;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Domain\Components\Middleware\HealthMiddleware;
use Phalcon\Api\Domain\Components\Middleware\NotFoundMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenClaimsMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenPresenceMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenStructureMiddleware;
use Phalcon\Api\Domain\Components\Middleware\ValidateTokenUserMiddleware;
use Phalcon\Api\Domain\Services\Auth\LoginPostService;
use Phalcon\Api\Domain\Services\User\UserGetService;
use Phalcon\Api\Responder\JsonResponder;
use Phalcon\Cache\AdapterFactory;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Di\Di;
use Phalcon\Di\Service;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterFactory;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Router;
use Phalcon\Storage\SerializerFactory;

class Container extends Di
{
    /** @var string */
    public const APPLICATION = 'application';
    /**
     * Services
     */
    public const AUTH_LOGIN_POST_SERVICE = 'service.auth.login.post';
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
    public const LOGGER = 'logger';
    /**
     * Middleware
     */
    public const MIDDLEWARE_HEALTH                  = HealthMiddleware::class;
    public const MIDDLEWARE_NOT_FOUND               = NotFoundMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_CLAIMS   = ValidateTokenClaimsMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_PRESENCE = ValidateTokenPresenceMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_STRUCTURE = ValidateTokenStructureMiddleware::class;
    public const MIDDLEWARE_VALIDATE_TOKEN_USER      = ValidateTokenUserMiddleware::class;
    /**
     * Repositories
     */
    public const REPOSITORY           = 'repository';
    public const REPOSITORY_TRANSPORT = TransportRepository::class;
    /** @var string */
    public const REQUEST = 'request';
    /**
     * Responders
     */
    public const RESPONDER_JSON = JsonResponder::class;
//    public const MIDDLEWARE_VALIDATE_TOKEN_REVOKED   = ValidateTokenRevokedMiddleware::class;
    /** @var string */
    public const RESPONSE = 'response';
    /** @var string */
    public const ROUTER = 'router';
    /** @var string */
    public const SECURITY = Security::class;
    /** @var string */
    public const TIME = 'time';
    public const USER_GET_SERVICE        = 'service.user.get';

    public function __construct()
    {
        $this->services = [
            self::CACHE          => $this->getServiceCache(),
            self::CONNECTION     => $this->getServiceConnection(),
            self::ENV            => $this->getServiceEnv(),
            self::EVENTS_MANAGER => $this->getServiceEventsManger(),
            self::FILTER         => $this->getServiceFilter(),
            self::JWT_TOKEN      => $this->getServiceJWTToken(),
            self::LOGGER         => $this->getServiceLogger(),
            self::REQUEST        => new Service(Request::class, true),
            self::RESPONSE       => new Service(Response::class, true),
            self::ROUTER         => $this->getServiceRouter(),

            self::REPOSITORY => $this->getServiceRepository(),

            self::AUTH_LOGIN_POST_SERVICE => $this->getServiceAuthLoginPost(),
            self::USER_GET_SERVICE        => $this->getServiceUserGet(),
        ];

        parent::__construct();
    }

    /**
     * @return Service
     */
    private function getServiceAuthLoginPost(): Service
    {
        return new Service(
            [
                'className' => LoginPostService::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => self::REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::REPOSITORY_TRANSPORT,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::CACHE,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::ENV,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::JWT_TOKEN,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::FILTER,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::SECURITY,
                    ],
                ],
            ]
        );
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
                $lifetime = $env->get('CACHE_LIFETIME', Cache::CACHE_LIFETIME_DAY, 'int');
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
    private function getServiceEnv(): Service
    {
        return new Service(
            function () {
                return new EnvManager();
            },
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceEventsManger(): Service
    {
        return new Service(
            function () {
                $evm = new EventsManager();
                $evm->enablePriorities(true);

                return $evm;
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
    private function getServiceJWTToken(): Service
    {
        return new Service(
            [
                'className' => JWTToken::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => self::ENV,
                    ],
                ],
            ]
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

    /**
     * @return Service
     */
    private function getServiceRepository(): Service
    {
        return new Service(
            [
                'className' => QueryRepository::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => self::CONNECTION,
                    ],
                ],
            ]
        );
    }

    /**
     * @return Service
     */
    private function getServiceRouter(): Service
    {
        return new Service(
            [
                'className' => Router::class,
                'arguments' => [
                    [
                        'type'  => 'parameter',
                        'value' => false,
                    ],
                ],
            ]
        );
    }

    /**
     * @return Service
     */
    private function getServiceUserGet(): Service
    {
        return new Service(
            [
                'className' => UserGetService::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => self::REPOSITORY,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::REPOSITORY_TRANSPORT,
                    ],
                    [
                        'type' => 'service',
                        'name' => self::FILTER,
                    ],
                ],
            ]
        );
    }
}
