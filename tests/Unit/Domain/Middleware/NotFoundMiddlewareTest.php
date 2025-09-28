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

use Phalcon\Api\Domain\Middleware\NotFoundMiddleware;
use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Http\HttpCodesEnum;
use Phalcon\Api\Tests\Unit\AbstractUnitTestCase;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use PHPUnit\Framework\Attributes\BackupGlobals;

use function ob_get_clean;
use function ob_start;

#[BackupGlobals(true)]
final class NotFoundMiddlewareTest extends AbstractUnitTestCase
{
    public function testBeforeNotFound(): void
    {
        $container   = new Container();
        $application = new Micro($container);
        $middleware  = new NotFoundMiddleware();

        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'REQUEST_URI'        => '/unknown',
        ];

        ob_start();
        $actual   = $middleware->beforeNotFound(
            new Event('ev1'),
            $application
        );
        $contents = ob_get_clean();

        $this->assertFalse($actual);

        $contents = json_decode($contents, true);

        $expected = [HttpCodesEnum::AppResourceNotFound->error()];
        $actual   = $contents['errors'];
        $this->assertSame($expected, $actual);
    }

    public function testCall(): void
    {
        $container   = new Container();
        $application = new Micro($container);
        $middleware  = new NotFoundMiddleware();

        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'REQUEST_URI'        => '/unknown',
        ];

        ob_start();
        $actual = $middleware->call($application);
        ob_get_clean();

        $this->assertTrue($actual);
    }
}
