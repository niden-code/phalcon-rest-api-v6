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

namespace Phalcon\Api\Domain\Services;

use Phalcon\Api\Domain\ADR\Responder\JsonResponder;
use Phalcon\Api\Domain\DataSource\User\UserRepository;
use Phalcon\Api\Domain\Hello\HelloService;
use Phalcon\Api\Domain\Middleware\HealthMiddleware;
use Phalcon\Api\Domain\Middleware\NotFoundMiddleware;
use Phalcon\Api\Domain\Middleware\ResponseSenderMiddleware;
use Phalcon\Api\Domain\Services\Env\EnvManager;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Api\Domain\User\UserGetService;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Di\Di;
use Phalcon\Di\Service;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterFactory;
use Phalcon\Http\Request;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Router;

class Container extends Di
{
    /** @var string */
    public const APPLICATION = 'application';
    /** @var string */
    public const CACHE = 'cache';
    /** @var string */
    public const CONNECTION = 'connection';
    /** @var string */
    public const EVENTS_MANAGER = 'eventsManager';
    /** @var string */
    public const FILTER = 'filter';
    /**
     * Services
     */
    public const HELLO_SERVICE    = HelloService::class;
    /** @var string */
    public const LOGGER = 'logger';
    /**
     * Middleware
     */
    public const MIDDLEWARE_HEALTH          = HealthMiddleware::class;
    public const MIDDLEWARE_NOT_FOUND       = NotFoundMiddleware::class;
    public const MIDDLEWARE_RESPONSE_SENDER = ResponseSenderMiddleware::class;
    /**
     * Repositories
     */
    public const REPOSITORY_USER = 'repository.user';
    /** @var string */
    public const REQUEST = 'request';
    /**
     * Responders
     */
    public const RESPONDER_JSON = JsonResponder::class;
    /** @var string */
    public const RESPONSE = 'response';
    /** @var string */
    public const ROUTER = 'router';
    /** @var string */
    public const TIME = 'time';
    public const USER_GET_SERVICE = 'service.user.get';

    public function __construct()
    {
        $this->services = [
            self::CONNECTION     => $this->getServiceConnection(),
            self::EVENTS_MANAGER => $this->getServiceEventsManger(),
            self::FILTER         => $this->getServiceFilter(),
            self::LOGGER         => $this->getServiceLogger(),
            self::REQUEST        => new Service(Request::class, true),
            self::RESPONSE       => new Service(Response::class, true),
            self::ROUTER         => $this->getServiceRouter(),

            self::USER_GET_SERVICE => $this->getServiceUserGet(),
            self::REPOSITORY_USER  => $this->getServiceRepositoryUser(),
        ];

        parent::__construct();
    }

    /**
     * @return Service
     */
    private function getServiceConnection(): Service
    {
        return new Service(
            function () {
                /** @var string $dbName */
                $dbName = EnvManager::get('DB_NAME', 'phalcon');
                /** @var string $host */
                $host = EnvManager::get('DB_HOST', 'rest-db');
                /** @var string $password */
                $password = EnvManager::get('DB_PASS', 'secret');
                $port     = (int)EnvManager::get('DB_PORT', 3306);
                /** @var string $username */
                $username = EnvManager::get('DB_USER', 'root');
                /** @var string $encoding */
                $encoding = EnvManager::get('DB_CHARSET', 'utf8');
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
    private function getServiceLogger(): Service
    {
        /** @var string $logName */
        $logName = EnvManager::get('LOG_FILENAME', 'rest-api');
        /** @var string $logPath */
        $logPath = EnvManager::get('LOG_PATH', 'storage/logs/');
        $logFile = EnvManager::appPath($logPath) . '/' . $logName . '.log';

        return new Service(
            function () use ($logName, $logFile) {
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
    private function getServiceRepositoryUser(): Service
    {
        return new Service(
            [
                'className' => UserRepository::class,
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
                        'name' => self::REPOSITORY_USER,
                    ],
                ],
            ]
        );
    }
}
