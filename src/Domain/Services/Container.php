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

use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Di\Di;
use Phalcon\Di\Service;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\FilterFactory;
use Phalcon\Http\Message\Request;
use Phalcon\Http\Message\Response;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Router\Route;
use Phalcon\Storage\SerializerFactory;

use function array_merge;
use function sprintf;

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
    /** @var string */
    public const LOGGER = 'logger';
    /** @var string */
    public const REQUEST = 'request';
    /** @var string */
    public const RESPONSE = 'response';
    /** @var string */
    public const ROUTER = 'router';
    /** @var string */
    public const SECURITY = 'security';
    /** @var string */
    public const TIME = 'time';

    /**
     * @throws InvalidConfigurationArguments
     */
    public function __construct()
    {
        /** @var array<string, Service> $services */
        $services = $this->services;

        $this->services = array_merge(
            [
                self::LOGGER         => $this->getServiceLogger(),
                self::CACHE          => $this->getServiceCache(),
                self::CONNECTION     => $this->getServiceConnection(),
                self::EVENTS_MANAGER => $this->getServiceEventsManger(),
                self::FILTER         => $this->getServiceFilter(),
                self::REQUEST        => $this->getServiceRequest(),
                self::RESPONSE       => $this->getServiceResponse(),
                self::ROUTER         => $this->getServiceRouter(),
                self::SECURITY       => $this->getServiceSecurity(),
            ],
            $services
        );

        parent::__construct();
    }

    /**
     * @return Service
     * @throws InvalidConfigurationArguments
     */
    private function getServiceCache(): Service
    {
        $adapter = EnvManager::getString('CACHE_ADAPTER', 'redis');
        $options = EnvManager::getCacheOptions();
        return new Service(
            function () use ($adapter, $options) {
                return new Cache(
                    (new AdapterFactory(new SerializerFactory()))
                        ->newInstance($adapter, $options)
                );
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
                $dbname   = EnvManager::getString('DB_NAME', 'phalcon');
                $host     = EnvManager::getString('DB_HOST', 'rest-db');
                $password = EnvManager::getString('DB_PASSWORD', 'secret');
                $port     = (int)EnvManager::get('DB_PORT', 3306);
                $username = EnvManager::getString('DB_USER', 'phalcon');
                $encoding = 'utf8';
                $queries  = ['SET NAMES utf8mb4'];
                $dsn      = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s;port=%s",
                    $host,
                    $dbname,
                    $encoding,
                    $port
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
                $em = new EventsManager();
                $em->enablePriorities(true);

                return $em;
            }
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
                $fileName = EnvManager::getString('USER_LOG_FILENAME', 'rest');
                $logPath  = EnvManager::getString('USER_LOG_PATH', 'storage/logs');
                $logFile  = EnvManager::appPath($logPath)
                    . '/' . $fileName . '.log';

                return new Logger(
                    $fileName,
                    [
                        'main' => new Stream($logFile),
                    ]
                );
            },
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceRequest(): Service
    {
        return new Service(
            [
                'className' => Request::class,
            ],
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceResponse(): Service
    {
        return new Service(
            [
                'className' => Response::class,
            ],
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceRouter(): Service
    {
        return new Service(
            [
                'className' => Route::class,
            ],
            true
        );
    }

    /**
     * @return Service
     */
    private function getServiceSecurity(): Service
    {
        return new Service(
            [
                'className' => Security::class,
            ],
            true
        );
    }
}
