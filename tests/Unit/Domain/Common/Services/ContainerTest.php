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

namespace Phalcon\Api\Tests\Unit\Domain\Common\Services\Environment;

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Tests\Fixtures\Domain\AbstractUnitTestCase;
use Phalcon\Cache\Cache;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter\Filter;
use Phalcon\Http\Message\Request;
use Phalcon\Http\Message\Response;
use Phalcon\Logger\Logger;
use Phalcon\Mvc\Router;

final class ContainerTest extends AbstractUnitTestCase
{
    /**
     * @return void
     */
    public function testServices(): void
    {
        $container = new Container();
        $services  = $container->getServices();

        $expected = 9;
        $actual   = count($services);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testRegisteredServices()
    {
        $container = new Container();

        $actual = $container->has(Container::LOGGER);
        $this->assertTrue($actual);

        $actual = $container->has(Container::CACHE);
        $this->assertTrue($actual);

        $actual = $container->has(Container::CONNECTION);
        $this->assertTrue($actual);

        $actual = $container->has(Container::EVENTS_MANAGER);
        $this->assertTrue($actual);

        $actual = $container->has(Container::FILTER);
        $this->assertTrue($actual);

        $actual = $container->has(Container::REQUEST);
        $this->assertTrue($actual);

        $actual = $container->has(Container::RESPONSE);
        $this->assertTrue($actual);

        $actual = $container->has(Container::ROUTER);
        $this->assertTrue($actual);

        $actual = $container->has(Container::SECURITY);
        $this->assertTrue($actual);

        $actual = $container->get(Container::LOGGER);
        $this->assertTrue($actual instanceof Logger);

        $actual = $container->get(Container::CACHE);
        $this->assertTrue($actual instanceof Cache);

        $actual = $container->get(Container::CONNECTION);
        $this->assertTrue($actual instanceof Connection);

        $actual = $container->get(Container::EVENTS_MANAGER);
        $this->assertTrue($actual instanceof EventsManager);

        $actual = $container->get(Container::FILTER);
        $this->assertTrue($actual instanceof Filter);

        $actual = $container->get(Container::REQUEST);
        $this->assertTrue($actual instanceof Request);

        $actual = $container->get(Container::RESPONSE);
        $this->assertTrue($actual instanceof Response);

        $actual = $container->get(Container::ROUTER);
        $this->assertTrue($actual instanceof Router);

        $actual = $container->get(Container::SECURITY);
        $this->assertTrue($actual instanceof Security);
    }
}
