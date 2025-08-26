<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Api\Domain\Services\Environment;

use Phalcon\Api\Domain\Services\Environment\Adapter\AdapterInterface;
use Phalcon\Api\Domain\Services\Environment\Adapter\Dotenv;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;

/**
 * Factory for creating environment configuration adapters.
 */
class EnvFactory
{
    /**
     * @var array<string, class-string>
     */
    protected array $mapper = [];

    /**
     * @var array<string, AdapterInterface>
     */
    protected array $services = [];

    /**
     * @param array<string, class-string> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the object
     *
     * @param string $name
     * @param mixed  ...$parameters
     *
     * @return AdapterInterface
     * @throws InvalidConfigurationArguments
     */
    public function newInstance(string $name, mixed ...$parameters): AdapterInterface
    {
        if (true !== isset($this->services[$name])) {
            $definition = $this->getService($name);

            /** @var AdapterInterface $instance */
            $instance              = new $definition(...$parameters);
            $this->services[$name] = $instance;
        }

        return $this->services[$name];
    }

    /**
     * @return array<string, class-string>
     */
    protected function getAdapters(): array
    {
        return [
            'dotenv' => Dotenv::class,
        ];
    }

    /**
     * Return a service from the mapper - if it does not exist
     * throws an exception
     *
     * @param string $name
     *
     * @return class-string
     * @throws InvalidConfigurationArguments
     */
    protected function getService(string $name): string
    {
        if (true !== isset($this->mapper[$name])) {
            throw new InvalidConfigurationArguments("Service " . $name . " is not registered");
        }

        return $this->mapper[$name];
    }

    /**
     * AdapterFactory constructor.
     *
     * @param array<string, string> $services
     */
    protected function init(array $services = []): void
    {
        $adapters = $this->getAdapters();
        $adapters = $adapters + $services;
        /**
         * @var string       $name
         * @var class-string $service
         */
        foreach ($adapters as $name => $service) {
            $this->mapper[$name] = $service;
            unset($this->services[$name]);
        }
    }
}
