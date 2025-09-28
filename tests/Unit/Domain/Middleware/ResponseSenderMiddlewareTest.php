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

namespace Phalcon\Api\Tests\Unit\Domain\Middleware;

use Phalcon\Api\Domain\Middleware\ResponseSenderMiddleware;
use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Api\Tests\Unit\AbstractUnitTestCase;
use Phalcon\Mvc\Micro;
use PHPUnit\Framework\Attributes\BackupGlobals;

use function ob_get_clean;
use function ob_start;
use function uniqid;

#[BackupGlobals(true)]
final class ResponseSenderMiddlewareTest extends AbstractUnitTestCase
{
    public function testCall(): void
    {
        $container   = new Container();
        $application = new Micro($container);
        $middleware  = new ResponseSenderMiddleware();
        /** @var Response $response */
        $response = $container->getShared(Container::RESPONSE);

        $content = uniqid('content-');
        $response->setContent($content);

        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'REQUEST_URI'        => '/health',
        ];

        ob_start();
        $actual   = $middleware->call($application);
        $contents = ob_get_clean();

        $this->assertTrue($actual);

        $expected = $content;
        $actual   = $contents;
        $this->assertEquals($expected, $actual);
    }
}
