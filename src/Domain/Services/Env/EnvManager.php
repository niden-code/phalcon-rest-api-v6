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

namespace Phalcon\Api\Domain\Services\Env;

use function array_merge;
use function getenv;

/**
 * @phpstan-import-type TSettings from EnvManagerTypes
 */
class EnvManager
{
    private static bool $isLoaded = false;

    /**
     * @var TSettings
     */
    private static array $settings = [];

    /**
     * @param string $path
     *
     * @return string
     */
    public static function appPath(string $path = ''): string
    {
        return dirname(__DIR__, 4)
            . ($path ? DIRECTORY_SEPARATOR . $path : $path)
        ;
    }

    /**
     * @param string               $key
     * @param bool|int|string|null $defaultValue
     *
     * @return bool|int|string|null
     */
    public static function get(
        string $key,
        bool | int | string | null $defaultValue = null
    ): bool | int | string | null {
        self::load();

        return self::$settings[$key] ?? $defaultValue;
    }

    /**
     * @return void
     */
    private static function load(): void
    {
        if (true !== self::$isLoaded) {
            self::$isLoaded = true;

            $envFactory = new EnvFactory();
            $options    = self::getOptions();
            $adapter    = $options['adapter'];

            $envs    = array_merge(getenv(), $_ENV);
            /** @var TSettings $options */
            $options = $envFactory->newInstance($adapter)->load($options);
            /** @var TSettings $envs */
            $envs    = array_merge($envs, $options);

            self::$settings = array_map(
                function ($value) {
                    return match ($value) {
                        'true' => true,
                        'false' => false,
                        default => $value,
                    };
                },
                $envs
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private static function getOptions(): array
    {
        $envs     = array_merge(getenv(), $_ENV);
        /** @var string $adapter */
        $adapter  = $envs['APP_ENV_ADAPTER'] ?? 'dotenv';
        /** @var string $filePath */
        $filePath = $envs['APP_ENV_FILE_PATH'] ?? '';

        return [
            'adapter'  => $adapter,
            'filePath' => $filePath,
        ];
    }
}
