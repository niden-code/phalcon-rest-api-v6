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

namespace Phalcon\Api\Domain\ADR\Responder;

use Exception as BaseException;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type TResult from ResponderTypes
 */
final class JsonResponder implements ResponderInterface
{
    /**
     * @param Response $response
     * @param Payload  $payload
     *
     * @return Response
     * @throws BaseException
     */
    public function __invoke(
        Response $response,
        Payload $payload
    ): Response {
        $result = $payload->getResult();
        /** @var TResult $content */
        $content = $result['results'];

        $response
            ->withPayloadData($content)
            ->render()
        ;

        return $response;
    }
}
