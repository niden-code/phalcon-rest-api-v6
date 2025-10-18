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

namespace Phalcon\Api\Tests\Unit\Domain\Components\Enums\Http;

use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HttpCodesEnumTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                HttpCodesEnum::OK,
                200,
                'OK',
            ],
            [
                HttpCodesEnum::BadRequest,
                400,
                'Bad Request',
            ],
            [
                HttpCodesEnum::NotFound,
                404,
                'Not Found',
            ],
            [
                HttpCodesEnum::Unauthorized,
                401,
                'Unauthorized',
            ],
            [
                HttpCodesEnum::AppMalformedPayload,
                3400,
                'Malformed payload',
            ],
            [
                HttpCodesEnum::AppRecordsNotFound,
                3401,
                'Record(s) not found',
            ],
            [
                HttpCodesEnum::AppResourceNotFound,
                3402,
                'Resource not found',
            ],
            [
                HttpCodesEnum::AppUnauthorized,
                3403,
                'Unauthorized',
            ],
            [
                HttpCodesEnum::AppInvalidArguments,
                3409,
                'Invalid arguments provided',
            ],
        ];
    }

    public function testCheckCount(): void
    {
        $expected = 9;
        $actual   = HttpCodesEnum::cases();
        $this->assertCount($expected, $actual);
    }

    #[DataProvider('getExamples')]
    public function testCheckItems(
        HttpCodesEnum $element,
        int $value,
        string $text
    ) {
        $expected = $value;
        $actual   = $element->value;
        $this->assertSame($expected, $actual);

        $expected = $text;
        $actual   = $element->text();
        $this->assertSame($expected, $actual);

        $expected = [$value => $text];
        $actual   = $element->error();
        $this->assertSame($expected, $actual);
    }
}
