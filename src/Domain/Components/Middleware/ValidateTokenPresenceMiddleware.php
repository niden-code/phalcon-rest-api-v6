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

namespace Phalcon\Api\Domain\Components\Middleware;

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Exception;
use Phalcon\Mvc\Micro;

final class ValidateTokenPresenceMiddleware extends AbstractMiddleware
{
    /**
     * @param Micro $application
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function call(Micro $application): bool
    {
        /** @var Request $request */
        $request = $application->getSharedService(Container::REQUEST);
        /** @var EnvManager $env */
        $env = $application->getSharedService(Container::ENV);

        if (true === $this->isEmptyBearerToken($request, $env)) {
            $this->halt(
                $application,
                HttpCodesEnum::Unauthorized->value,
                HttpCodesEnum::Unauthorized->text(),
                [],
                [HttpCodesEnum::AppTokenNotPresent->error()]
            );

            return false;
        }

        return true;
    }
}
