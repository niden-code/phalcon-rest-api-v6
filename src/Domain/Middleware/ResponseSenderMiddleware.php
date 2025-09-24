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

use Phalcon\Api\Domain\Interfaces\ActionInterface;
use Phalcon\Api\Domain\Interfaces\DomainInterface;
use Phalcon\Api\Domain\Interfaces\ResponderInterface;
use Phalcon\Http\ResponseInterface;

final readonly class ResponseSender
{
    public function __invoke(ResponseInterface $response): ResponseInterface
    {
        return $response->send();
    }
}
