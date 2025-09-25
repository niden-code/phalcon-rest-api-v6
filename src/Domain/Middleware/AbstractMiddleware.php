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
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    protected function halt(
        Micro $application,
        int $code,
        string $message = '',
        array $data = [],
        array $errors = []
    ): void {
        /** @var Response $response */
        $response = $application->getSharedService(Container::RESPONSE);

        $application->stop();

        $response->withCode($code, $message);

        if (true === empty($errors)) {
            $response->withPayloadData($data);
        } else {
            $response->withPayloadErrors($errors);
        }

        $response->render()->send();
    }
}
