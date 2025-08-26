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

use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;

/**
 * Interface for Env adapters
 *
 * @phpstan-import-type TSettings from EnvManager
 */
interface AdapterInterface
{
    /**
     * @param array<string, string> $options
     *
     * @return TSettings
     * @throws InvalidConfigurationArguments
     */
    public function load(array $options): array;
}
