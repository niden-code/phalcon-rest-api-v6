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

namespace Phalcon\Api\Responder\Hello;

use Phalcon\Http\Response;

final class HelloTextResponder
{
    public function __construct(
        private Response $response
    ) {
    }

    public function __invoke(string $payload): Response
    {
        $this->response->setContent($payload);

        return $this->response;
    }
}
