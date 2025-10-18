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

namespace Phalcon\Api\Tests\Unit\Domain\Components\Env;

use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use ReflectionClass;

#[BackupGlobals(true)]
final class EnvManagerTest extends AbstractUnitTestCase
{
    public function setUp(): void
    {
        $ref = new ReflectionClass(EnvManager::class);
        $ref->setStaticPropertyValue('isLoaded', false);
        $ref->setStaticPropertyValue('settings', []);
    }

    public function testAppEnvReturnsDefault(): void
    {
        $expected = 'development';
        $actual   = EnvManager::appEnv();
        $this->assertSame($expected, $actual);
    }

    public function testAppEnvReturnsValue(): void
    {
        $_ENV = ['APP_ENV' => 'production'];

        $expected = 'production';
        $actual   = EnvManager::appEnv();
        $this->assertSame($expected, $actual);
    }

    public function testAppLogLevelReturnsDefault(): void
    {
        $expected = 1;
        $actual   = EnvManager::appLogLevel();
        $this->assertSame($expected, $actual);
    }

    public function testAppLogLevelReturnsValue(): void
    {
        $_ENV = ['APP_LOG_LEVEL' => 5];

        $expected = 5;
        $actual   = EnvManager::appLogLevel();
        $this->assertSame($expected, $actual);
    }

    public function testAppPathReturnsRoot(): void
    {
        $expected = dirname(__DIR__, 5);
        $actual   = EnvManager::appPath();
        $this->assertSame($expected, $actual);
    }

    public function testAppTimezoneReturnsDefault(): void
    {
        $expected = 'UTC';
        $actual   = EnvManager::appTimezone();
        $this->assertSame($expected, $actual);
    }

    public function testAppTimezoneReturnsValue(): void
    {
        $_ENV = ['APP_TIMEZONE' => 'America/Los_Angeles'];

        $expected = 'America/Los_Angeles';
        $actual   = EnvManager::appTimezone();
        $this->assertSame($expected, $actual);
    }

    public function testGetFromDotEnvLoad(): void
    {
        $_ENV = [
            'APP_ENV_ADAPTER'   => 'dotenv',
            'APP_ENV_FILE_PATH' => EnvManager::appPath()
                . '/tests/Fixtures/Domain/Components/Env/',
        ];

        $values = [
            'SAMPLE_STRING' => 'sample_value',
            'SAMPLE_INT'    => '1',
            'SAMPLE_TRUE'   => true,
            'SAMPLE_FALSE'  => false,
        ];

        $expected = 'default_value';
        $actual   = EnvManager::get('NON_EXISTENT', 'default_value');
        $this->assertSame($expected, $actual);

        $expected = $values['SAMPLE_STRING'];
        $actual   = EnvManager::get('SAMPLE_STRING');
        $this->assertSame($expected, $actual);

        $expected = $values['SAMPLE_INT'];
        $actual   = EnvManager::get('SAMPLE_INT');
        $this->assertSame($expected, $actual);

        $expected = $values['SAMPLE_TRUE'];
        $actual   = EnvManager::get('SAMPLE_TRUE');
        $this->assertSame($expected, $actual);

        $expected = $values['SAMPLE_FALSE'];
        $actual   = EnvManager::get('SAMPLE_FALSE');
        $this->assertSame($expected, $actual);
    }
}
