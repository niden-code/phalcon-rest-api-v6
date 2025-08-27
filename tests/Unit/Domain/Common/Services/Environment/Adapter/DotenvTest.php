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

namespace Phalcon\Api\Tests\Unit\Domain\Common\Services\Environment\Adapter;

use Phalcon\Api\Domain\Services\Environment\Adapter\Dotenv;
use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;
use Phalcon\Api\Tests\Fixtures\Domain\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class DotenvTest extends AbstractUnitTestCase
{
    private string $envFile;

    public function testLoadReturnsEnvArray(): void
    {
        $dotenv   = new Dotenv();
        $options  = ['filePath' => $this->envFile];
        $expected = [
            'SAMPLE_STRING' => 'sample_value',
            'SAMPLE_INT'    => '1',
            'SAMPLE_TRUE'   => 'true',
            'SAMPLE_FALSE'  => 'false',
        ];

        $actual = $dotenv->load($options);

        $this->assertArrayHasKey('SAMPLE_STRING', $actual);
        $this->assertArrayHasKey('SAMPLE_INT', $actual);
        $this->assertArrayHasKey('SAMPLE_TRUE', $actual);
        $this->assertArrayHasKey('SAMPLE_FALSE', $actual);

        $this->assertSame($expected['SAMPLE_STRING'], $actual['SAMPLE_STRING']);
        $this->assertSame($expected['SAMPLE_INT'], $actual['SAMPLE_INT']);
        $this->assertSame($expected['SAMPLE_TRUE'], $actual['SAMPLE_TRUE']);
        $this->assertSame($expected['SAMPLE_FALSE'], $actual['SAMPLE_FALSE']);
    }

    public function testLoadThrowsExceptionForEmptyFilePath()
    {
        $this->expectException(InvalidConfigurationArguments::class);
        $this->expectExceptionMessage(
            'The .env file does not exist at the specified path: '
        );

        $dotenv  = new Dotenv();
        $options = ['filePath' => ''];
        $dotenv->load($options);
    }

    public function testLoadThrowsExceptionForMissingFile()
    {
        $this->expectException(InvalidConfigurationArguments::class);
        $this->expectExceptionMessage(
            'The .env file does not exist at the specified path: /nonexistent/path/.env'
        );

        $dotenv  = new Dotenv();
        $options = ['filePath' => '/nonexistent/path/.env'];
        $dotenv->load($options);
    }

    protected function setUp(): void
    {
        $this->envFile = EnvManager::appPath()
            . '/tests/Fixtures/Domain/Services/Environment/';
    }
}
