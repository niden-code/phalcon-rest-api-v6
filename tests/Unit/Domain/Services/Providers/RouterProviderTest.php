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

namespace Phalcon\Api\Tests\Unit\Domain\Services\Providers;

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Providers\RouterProvider;
use Phalcon\Api\Tests\Unit\AbstractUnitTestCase;
use Phalcon\Mvc\Micro;

final class RouterProviderTest extends AbstractUnitTestCase
{
    public function testCheckRegistration(): void
    {
        $container   = new Container();
        $application = new Micro($container);
        $container->setShared(Container::APPLICATION, $application);

        $provider = new RouterProvider();
        $provider->register($container);

        $router = $application->getRouter();
        $routes = $router->getRoutes();

        $data = [
            [
                'method'  => 'GET',
                'pattern' => '/',
            ],
            [
                'method'  => 'GET',
                'pattern' => '/health',
            ],
        ];

        $expected = count($data);
        $actual   = count($routes);
        $this->assertSame($expected, $actual);

        foreach ($data as $index => $route) {
            $expected = $route['method'];
            $actual   = $routes[$index]->getHttpMethods();
            $this->assertSame($expected, $actual);

            $expected = $route['pattern'];
            $actual   = $routes[$index]->getPattern();
            $this->assertSame($expected, $actual);
        }
    }
}
