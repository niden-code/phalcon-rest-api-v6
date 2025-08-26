<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Api\Domain\Services\Environment;

use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;

/**
 * Loads environment variables from various sources such as .env files or
 * AWS Secrets Manager.
 *
 * The class reads the `APP_ENV_ADAPTER` environment variable to determine
 * what adapter needs to be read. The adapters are:
 *
 * - `dotenv`: Reads from a `.env` file. Requires the `APP_ENV_FILE_PATH`
 *   environment variable to be set. If it is not set, it will try and read
 *   it from the root of the application.
 * - `aws-secrets-manager`: Reads from AWS Secrets Manager. Requires the
 *   following:
 *   - `AWS_REGION`: The AWS region to connect to.
 *   - `AWS_ACCESS_KEY`: The AWS access key.
 *   - `AWS_SECRET`: The AWS secret key.
 *
 * @phpstan-type TSettings array<string, bool|int|string|null>
 * @phpstan-type  TOptions array<string, int|string>
 */
class EnvManager
{
    private static bool $isLoaded = false;

    /**
     * @var TSettings
     */
    private static array $settings = [];

    /**
     * Get the application environment.
     *
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function appEnv(): string
    {
        self::load();

        return self::getString('APP_ENV', 'development');
    }

    /**
     * Get the application level
     *
     * @return int
     * @throws InvalidConfigurationArguments
     */
    public static function appLogLevel(): int
    {
        self::load();

        return self::getInt('APP_LOG_LEVEL', 1);
    }

    /**
     * Get the application path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function appPath(string $path = ''): string
    {
        return dirname(__DIR__, 4)
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Returns the application start time in nanoseconds.
     *
     * @return int
     */
    public static function appTime(): int
    {
        return hrtime(true);
    }

    /**
     * Returns the application timezone.
     *
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function appTimezone(): string
    {
        self::load();

        return self::getString('APP_TIMEZONE', 'UTC');
    }

    /**
     * Returns the application version.
     *
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function appVersion(): string
    {
        self::load();

        return self::getString('APP_VERSION', '1.0.0');
    }

    /**
     * @param string               $key
     * @param bool|int|string|null $defaultValue
     *
     * @return bool|int|string|null
     * @throws InvalidConfigurationArguments
     */
    public static function get(
        string $key,
        bool | int | string | null $defaultValue = null
    ): bool | int | string | null {
        self::load();

        return self::$settings[$key] ?? $defaultValue;
    }

    /**
     * Get cache options from environment variables.
     *
     * @return TOptions
     * @throws InvalidConfigurationArguments
     */
    public static function getCacheOptions(): array
    {
        self::load();

        return [
            'host'     => self::getString('REDIS_HOST', 'localhost'),
            'index'    => 1,
            'lifetime' => self::getInt('CACHE_LIFETIME', 86400),
            'prefix'   => self::getString('CACHE_PREFIX', '-rest-'),
            'port'     => self::getInt('REDIS_PORT', 6379),
            'uniqueId' => self::getString('CACHE_PREFIX', '-rest-'),
        ];
    }

    /**
     * @param string $key
     * @param string $defaultValue
     *
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function getInt(
        string $key,
        int $defaultValue = 0
    ): int {
        return (int)(self::get($key, $defaultValue));
    }

    /**
     * @param string $key
     * @param string $defaultValue
     *
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function getString(
        string $key,
        string $defaultValue = ''
    ): string {
        return (string)(self::get($key, $defaultValue));
    }

    /**
     * Returns the options for the AWS Secrets Manager adapter.
     *
     * @return array<string, string>
     */
    private static function getOptions(): array
    {
        $envs = array_merge(getenv(), $_ENV);

        /** @var string $filePath */
        $filePath = $envs['APP_ENV_FILE_PATH'] ?? self::appPath();

        return [
            'adapter'  => 'dotenv',
            'filePath' => $filePath,
        ];
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    private static function load(): void
    {
        if (true !== self::$isLoaded) {
            self::$isLoaded = true;

            $envFactory = new EnvFactory();
            $options    = self::getOptions();
            /** @var TOptions $envs */
            $envs = array_merge(getenv(), $_ENV);
            /** @var string $adapter */
            $adapter = $options['adapter'];
            /** @var TSettings $options */
            $options = $envFactory->newInstance($adapter)->load($options);
            /** @var TOptions $envs */
            $envs = array_merge($envs, $options);

            self::$settings = array_map(
                function ($value) {
                    return match ($value) {
                        'true'  => true,
                        'false' => false,
                        default => $value
                    };
                },
                $envs
            );
        }
    }
}
