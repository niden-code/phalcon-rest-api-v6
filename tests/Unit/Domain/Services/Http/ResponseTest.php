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

namespace Phalcon\Api\Tests\Unit\Domain\Services\Http;

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Api\Tests\Unit\AbstractUnitTestCase;

use function ob_get_clean;
use function ob_start;
use function uniqid;

final class ResponseTest extends AbstractUnitTestCase
{
    public function testWithPayloadMeta(): void
    {
        $container = new Container();
        /** @var Response $response */
        $response = $container->getShared(Container::RESPONSE);

        $message = uniqid('message-');
        $key     = uniqid('key-');
        $value   = uniqid('value-');

        $response
            ->withCode(404, $message)
            ->withPayloadData([$key => $value])
        ;

        ob_start();
        $response->render()->send();
        $data = ob_get_clean();

        $expected = 404;
        $actual   = $response->getStatusCode();
        $this->assertSame($expected, $actual);

        $expected = $message;
        $actual   = $response->getReasonPhrase();
        $this->assertSame($expected, $actual);

        /**
         * Remove the timestamp and hash because they are always changing
         */
        $data = json_decode($data, true);
        unset($data['meta']['timestamp']);
        unset($data['meta']['hash']);

        $expected = [
            'data'   => [
                $key => $value,
            ],
            'errors' => [],
            'meta'   => [
                'code'    => 200,
                'message' => 'success',
            ],
        ];
        $actual   = $data;
        $this->assertSame($expected, $actual);
    }

    public function testWithPayloadMetaErrors(): void
    {
        $container = new Container();
        /** @var Response $response */
        $response = $container->getShared(Container::RESPONSE);

        $message = uniqid('message-');

        $response
            ->withPayloadErrors([[$message]])
        ;

        ob_start();
        $response->render()->send();
        $data = ob_get_clean();

        /**
         * Remove the timestamp and hash because they are always changing
         */
        $data = json_decode($data, true);
        unset($data['meta']['timestamp']);
        unset($data['meta']['hash']);

        $expected = [
            'data'   => [],
            'errors' => [
                [
                    $message,
                ],
            ],
            'meta'   => [
                'code'    => 3000,
                'message' => 'error',
            ],
        ];
        $actual   = $data;
        $this->assertSame($expected, $actual);
    }
}
