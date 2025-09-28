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
use Phalcon\Api\Domain\Health\HealthService;
use Phalcon\Api\Domain\Hello\HelloService;
use Phalcon\Api\Domain\Middleware\HealthMiddleware;
use Phalcon\Api\Domain\Middleware\NotFoundMiddleware;
use Phalcon\Api\Domain\Middleware\ResponseSenderMiddleware;
use Phalcon\Api\Domain\Services\Env\EnvManager;
use Phalcon\Api\Domain\Services\Http\Response;
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
    public const HELLO_SERVICE = 'hello.service';
    /** @var string */
    public const LOGGER = 'logger';
    /**
     * Middleware
     */
    public const MIDDLEWARE_HEALTH          = 'middleware.health';
    public const MIDDLEWARE_NOT_FOUND       = 'middleware.not.found';
    public const MIDDLEWARE_RESPONSE_SENDER = 'middleware.response.sender';
    /** @var string */
    public const REQUEST = 'request';
    /**
     * Responders
     */
    public const RESPONDER_JSON = 'hello.responder.json';
    /** @var string */
    public const RESPONSE = 'response';
    /** @var string */
    public const ROUTER = 'router';
    /** @var string */
    public const TIME = 'time';

    public function __construct()
    {
        $this->services = [
            self::EVENTS_MANAGER => $this->getServiceEventsManger(),
            self::FILTER         => $this->getServiceFilter(),
            self::LOGGER         => $this->getServiceLogger(),
            self::REQUEST        => $this->getServiceSimple(Request::class, true),
            self::RESPONSE       => $this->getServiceSimple(Response::class, true),
            self::ROUTER         => $this->getServiceRouter(),

            self::HELLO_SERVICE => $this->getServiceSimple(HelloService::class),

            self::MIDDLEWARE_HEALTH          => $this->getServiceSimple(HealthMiddleware::class),
            self::MIDDLEWARE_NOT_FOUND       => $this->getServiceSimple(NotFoundMiddleware::class),
            self::MIDDLEWARE_RESPONSE_SENDER => $this->getServiceSimple(ResponseSenderMiddleware::class),

            self::RESPONDER_JSON => $this->getServiceResponderJson(),
        ];

        parent::__construct();
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

    private function getServiceResponderJson(): Service
    {
        return new Service(
            [
                'className' => JsonResponder::class,
                'arguments' => [
                    [
                        'type' => 'service',
                        'name' => self::RESPONSE,
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
     * @param string $className
     * @param bool   $isShared
     *
     * @return Service
     */
    private function getServiceSimple(
        string $className,
        bool $isShared = false
    ): Service {
        return new Service($className, $isShared);
    }
}
