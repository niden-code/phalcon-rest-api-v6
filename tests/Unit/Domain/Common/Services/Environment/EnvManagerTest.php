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

use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;
use Phalcon\Api\Tests\Fixtures\Domain\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use ReflectionClass;
use ReflectionException;

#[BackupGlobals(true)]
final class EnvManagerTest extends AbstractUnitTestCase
{
    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppEnvReturnsDefault(): void
    {
        $expected = 'development';
        $actual   = EnvManager::appEnv();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppEnvReturnsValue(): void
    {
        $_ENV     = ['APP_ENV' => 'production'];
        $expected = 'production';
        $actual   = EnvManager::appEnv();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppLogLevelReturnsDefault(): void
    {
        $expected = 1;
        $actual   = EnvManager::appLogLevel();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppLogLevelReturnsValue(): void
    {
        $_ENV     = ['APP_LOG_LEVEL' => 5];
        $expected = 5;
        $actual   = EnvManager::appLogLevel();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testAppPathReturnsRoot(): void
    {
        $expected = dirname(__DIR__, 6);
        $actual   = EnvManager::appPath();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testAppPathReturnsWithSubPath(): void
    {
        $expected = dirname(__DIR__, 6) . DIRECTORY_SEPARATOR . 'path';
        $actual   = EnvManager::appPath('path');
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testAppTimeReturnsInt(): void
    {
        $actual = EnvManager::appTime();
        $this->assertIsInt($actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppTimezoneReturnsDefault(): void
    {
        $expected = 'UTC';
        $actual   = EnvManager::appTimezone();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppTimezoneReturnsValue(): void
    {
        $_ENV     = ['APP_TIMEZONE' => 'America/New_York'];
        $expected = 'America/New_York';
        $actual   = EnvManager::appTimezone();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppVersionReturnsDefault(): void
    {
        $expected = '1.0.0';
        $actual   = EnvManager::appVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testAppVersionReturnsValue(): void
    {
        $_ENV     = ['APP_VERSION' => '2.3.4'];
        $expected = '2.3.4';
        $actual   = EnvManager::appVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testGetCacheOptionsReturnsDefaults(): void
    {
        $inCi = getenv('CIRCLECI');
        $host = 'true' === $inCi ? '127.0.0.1' : 'localhost';

        $expected = [
            'host'     => $host,
            'index'    => 1,
            'lifetime' => 86400,
            'prefix'   => '-rest-',
            'port'     => 6379,
            'uniqueId' => '-rest-',
        ];

        $actual = EnvManager::getCacheOptions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testGetCacheOptionsReturnsValues(): void
    {
        $_ENV = [
            'CACHE_PREFIX'   => 'prefix',
            'REDIS_HOST'     => 'redis',
            'CACHE_LIFETIME' => 123,
            'REDIS_PORT'     => 456,
        ];

        $expected = [
            'host'     => 'redis',
            'index'    => 1,
            'lifetime' => 123,
            'prefix'   => 'prefix',
            'port'     => 456,
            'uniqueId' => 'prefix',
        ];
        $actual   = EnvManager::getCacheOptions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testGetFromDotEnvLoad(): void
    {
        $_ENV = [
            'APP_ENV_ADAPTER'   => 'dotenv',
            'APP_ENV_FILE_PATH' => EnvManager::appPath()
                . '/tests/Fixtures/Domain/Services/Environment/',
        ];

        $values   = [
            'SAMPLE_STRING' => 'sample_value',
            'SAMPLE_INT'    => '1',
            'SAMPLE_TRUE'   => true,
            'SAMPLE_FALSE'  => false,
        ];
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

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testGetReturnsDefault(): void
    {
        $expected = 'default';
        $actual   = EnvManager::get('NON_EXISTENT', 'default');
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testGetReturnsValue(): void
    {
        $_ENV     = ['SAMPLE_ENV' => 'sample_value'];
        $expected = 'sample_value';
        $actual   = EnvManager::get('SAMPLE_ENV');
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        // Reset static properties before each test
        $ref = new ReflectionClass(EnvManager::class);
        $ref->setStaticPropertyValue('isLoaded', false);
        $ref->setStaticPropertyValue('settings', []);
    }
}
