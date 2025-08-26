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

namespace Phalcon\Api\Domain\Services\Environment\Adapter;

use Dotenv\Dotenv as ParentDotenv;
use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;

use function file_exists;

/**
 * Reads .env files and returns the array back
 *
 * @phpstan-import-type TSettings from EnvManager
 * @phpstan-type TDotEnvOptions array{
 *       filePath?: string
 *  }
 */
class Dotenv implements AdapterInterface
{
    /**
     * @param TDotEnvOptions $options
     *
     * @return TSettings
     * @throws InvalidConfigurationArguments
     */
    public function load(array $options): array
    {
        $filePath = $options['filePath'] ?? null;
        if (true === empty($filePath) || true !== file_exists($filePath)) {
            throw new InvalidConfigurationArguments(
                '"The .env file does not exist at the specified path: '
                . $filePath
            );
        }

        $dotenv = ParentDotenv::createImmutable($filePath);
        $dotenv->load();

        /** @var TSettings $env */
        $env = $_ENV;

        return $env;
    }
}
