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

namespace Phalcon\Api\Domain\Middleware;

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Http\Response\Exception;
use Phalcon\Mvc\Micro;

final class ResponseSenderMiddleware extends AbstractMiddleware
{
    /**
     * @param Micro $application
     *
     * @return true
     * @throws EventsException
     * @throws Exception
     */
    public function call(Micro $application): bool
    {
        /** @var Response $response */
        $response = $application->getSharedService(Container::RESPONSE);

        $response->send();

        return true;
    }
}
