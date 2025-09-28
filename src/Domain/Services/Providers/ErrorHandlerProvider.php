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

namespace Phalcon\Api\Domain\Services\Providers;

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Env\EnvManager;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger\Logger;

use function date_default_timezone_set;
use function error_reporting;
use function hrtime;
use function memory_get_usage;
use function number_format;
use function register_shutdown_function;
use function sprintf;

class ErrorHandlerProvider implements ServiceProviderInterface
{
    public function register(DiInterface $container): void
    {
        /** @var Logger $logger */
        $logger = $container->getShared(Container::LOGGER);

        date_default_timezone_set(EnvManager::appTimezone());
        $errors = 'development' === EnvManager::appEnv() ? 'On' : 'Off';
        ini_set('display_errors', $errors);
        error_reporting(E_ALL);

        set_error_handler(
            function (int $number, string $message, string $file, int $line) use ($logger) {
                $logger
                    ->error(
                        sprintf(
                            '[#:%s]-[L: %s] : %s (%s)',
                            $number,
                            $line,
                            $message,
                            $file
                        )
                    )
                ;

                return true;
            }
        );

        register_shutdown_function([$this, 'onShutdown'], $container);
    }

    protected function onShutdown(DiInterface $container): bool
    {
        /** @var Logger $logger */
        $logger = $container->getShared(Container::LOGGER);
        /** @var int $time */
        $time      = $container->getShared(Container::TIME);
        $memory    = memory_get_usage() / 1000000;
        $execution = hrtime(true) - $time;
        $execution = $execution / 1000000000;

        if (EnvManager::appLogLevel() >= 1) {
            $logger
                ->info(
                    sprintf(
                        'Shutdown completed [%s]s - [%s]MB',
                        number_format($execution, 4),
                        number_format($memory, 2),
                    )
                )
            ;
        }

        return true;
    }
}
