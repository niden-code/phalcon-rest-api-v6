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

namespace Phalcon\Api\Tests\Unit\Domain\Services\Hello;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Domain\Services\Hello\HelloService;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;

use function ob_get_clean;
use function ob_start;
use function restore_error_handler;
use function time;

#[BackupGlobals(true)]
final class HelloServiceTest extends AbstractUnitTestCase
{
    public function testDispatch(): void
    {
        $time    = $_SERVER['REQUEST_TIME_FLOAT'] ?? time();
        $_SERVER = [
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_TIME_FLOAT' => $time,
            'REQUEST_URI'        => '/',
        ];

        ob_start();
        require_once EnvManager::appPath('public/index.php');
        $response = ob_get_clean();

        $contents = json_decode($response, true);

        restore_error_handler();

        $this->assertArrayHasKey('data', $contents);
        $this->assertArrayHasKey('errors', $contents);

        $data   = $contents['data'];
        $errors = $contents['errors'];

        $expected = [];
        $actual   = $errors;
        $this->assertSame($expected, $actual);

        $expected = 'Hello World!!! - ';
        $actual   = $data[0];
        $this->assertStringContainsString($expected, $actual);
    }

    public function testService(): void
    {
        /** @var HelloService $service */
        $service = $this->container->get(Container::HELLO_SERVICE);

        $payload = $service->__invoke([]);

        $expected = DomainStatus::SUCCESS;
        $actual   = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $actual = $payload->getResult();
        $this->assertArrayHasKey('data', $actual);

        $expected = 'Hello World!!! - ';
        $actual   = $actual['data'][0];
        $this->assertStringContainsString($expected, $actual);
    }
}
