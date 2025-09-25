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

namespace Phalcon\Api\Responder;

use Phalcon\Api\Domain\Interfaces\ResponderInterface;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Domain\Payload;

final class JsonResponder implements ResponderInterface
{
    public function __construct(
        private Response $response
    ) {
    }

    public function __invoke(Payload $payload): Response
    {
        $result = $payload->getResult();
        /** @var string $content */
        $content = $result['results'];

        $this
            ->response
            ->withPayloadData([$content])
            ->render()
        ;

        return $this->response;
    }
}
