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

use Phalcon\Api\Domain\Hello\HelloService;
use Phalcon\Api\Responder\JsonResponder;
use Phalcon\Di\Di;
use Phalcon\Di\Service;
use Phalcon\Filter\FilterFactory;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Router;

class Container extends Di
{
    /** @var string */
    public const CACHE = 'cache';
    /** @var string */
    public const CONNECTION = 'connection';
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

    /**
     * Hello
     */
    public const HELLO_SERVICE        = 'hello.service';
    public const HELLO_RESPONDER_JSON = 'hello.responder.json';

    public function __construct()
    {
        $this->services = [
            self::FILTER   => $this->getServiceFilter(),
            self::REQUEST  => $this->getServiceSimple(Request::class, true),
            self::RESPONSE => $this->getServiceSimple(Response::class, true),
            self::ROUTER   => $this->getServiceRouter(),

            self::HELLO_SERVICE        => $this->getServiceSimple(HelloService::class),
            self::HELLO_RESPONDER_JSON => $this->getServiceResponderJson(),
        ];

        parent::__construct();
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
    private function getServiceRouter(): Service
    {
        return new Service(
            [
                'className' => Router::class,
                'arguments' => [
                    [
                        'type'  => 'parameter',
                        'value' => false,
                    ]
                ]
            ]
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
                    ]
                ]
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
