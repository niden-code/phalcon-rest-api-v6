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
use Phalcon\Api\Domain\Services\Providers\RoutesEnum;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RoutesEnumTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                RoutesEnum::helloGet,
                '',
                '/',
                RoutesEnum::GET,
                Container::HELLO_SERVICE,
            ],
            [
                RoutesEnum::userGet,
                'user',
                '/user',
                RoutesEnum::GET,
                Container::USER_GET_SERVICE,
            ],
        ];
    }

    public function testCheckCount(): void
    {
        $expected = 2;
        $actual   = RoutesEnum::cases();
        $this->assertCount($expected, $actual);
    }

    #[DataProvider('getExamples')]
    public function testCheckItems(
        RoutesEnum $element,
        string $value,
        string $endpoint,
        string $method,
        string $service
    ) {
        $expected = $value;
        $actual   = $element->value;
        $this->assertSame($expected, $actual);

        $expected = $endpoint;
        $actual   = $element->endpoint();
        $this->assertSame($expected, $actual);

        $expected = $method;
        $actual   = $element->method();
        $this->assertSame($expected, $actual);

        $expected = $service;
        $actual   = $element->service();
        $this->assertSame($expected, $actual);
    }
}
