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

namespace Phalcon\Api\Domain\Services\Env\Adapters;

use Dotenv\Dotenv as ParentDotEnv;
use Exception;
use Phalcon\Api\Domain\Exceptions\InvalidConfigurationArgumentException;

class DotEnv implements AdapterInterface
{
    /**
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    public function load(array $options): array
    {
        /** @var string|null $filePath */
        $filePath = $options['filePath'] ?? null;
        if (true === empty($filePath) || true !== file_exists($filePath)) {
            throw new InvalidConfigurationArgumentException(
                'The .env file does not exist at the specified path: '
                . (string)$filePath
            );
        }

        $dotenv = ParentDotEnv::createImmutable($filePath);
        $dotenv->load();

        return $_ENV;
    }
}
