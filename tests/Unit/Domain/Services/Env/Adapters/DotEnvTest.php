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

namespace Phalcon\Api\Tests\Unit\Domain\Services\Env\Adapters;

use Phalcon\Api\Domain\Exceptions\InvalidConfigurationArgumentException;
use Phalcon\Api\Domain\Services\Env\Adapters\DotEnv;
use Phalcon\Api\Domain\Services\Env\EnvFactory;
use Phalcon\Api\Domain\Services\Env\EnvManager;
use Phalcon\Api\Tests\Unit\AbstractUnitTestCase;

final class DotEnvTest extends AbstractUnitTestCase
{
    private string $envFile;

    protected function setUp(): void
    {
        $this->envFile = EnvManager::appPath()
            . '/tests/Fixtures/Domain/Services/Env/'
        ;
    }

    public function testLoadSuccess(): void
    {
        $dotEnv = new DotEnv();
        $options = [
            'filePath' => $this->envFile,
        ];

        $expected = [
            'SAMPLE_STRING' => 'sample_value',
            'SAMPLE_INT'    => '1',
            'SAMPLE_TRUE'   => 'true',
            'SAMPLE_FALSE'  => 'false',
        ];
        $actual = $dotEnv->load($options);

        $this->assertArrayHasKey('SAMPLE_STRING', $actual);
        $this->assertArrayHasKey('SAMPLE_INT', $actual);
        $this->assertArrayHasKey('SAMPLE_TRUE', $actual);
        $this->assertArrayHasKey('SAMPLE_FALSE', $actual);

        $actualArray = [
            'SAMPLE_STRING' => $actual['SAMPLE_STRING'],
            'SAMPLE_INT'    => $actual['SAMPLE_INT'],
            'SAMPLE_TRUE'   => $actual['SAMPLE_TRUE'],
            'SAMPLE_FALSE'  => $actual['SAMPLE_FALSE'],
        ];

        $this->assertSame($expected, $actualArray);
    }

    public function testLoadExceptionForEmptyFilePath(): void
    {
        $this->expectException(InvalidConfigurationArgumentException::class);
        $this->expectExceptionMessage(
            'The .env file does not exist at the specified path'
        );

        $dotEnv = new DotEnv();
        $options = [
            'filePath' => '',
        ];

        $dotEnv->load($options);
    }

    public function testLoadExceptionForMissingFile(): void
    {
        $this->expectException(InvalidConfigurationArgumentException::class);
        $this->expectExceptionMessage(
            'The .env file does not exist at the specified path'
        );

        $dotEnv = new DotEnv();
        $options = [
            'filePath' => '/does/not/exist/',
        ];

        $dotEnv->load($options);
    }
}
