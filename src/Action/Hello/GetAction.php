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

namespace Phalcon\Api\Action\Hello;

use Phalcon\Api\Domain\Hello\HelloService;
use Phalcon\Api\Responder\Hello\HelloTextResponder;
use Phalcon\Http\Response;

final class GetAction
{
    public function __construct(
        private readonly HelloService $service,
        private readonly HelloTextResponder $responder
    ) {
    }

    public function __invoke(): void
    {
        $service   = $this->service;
        $responder = $this->responder;

        $serviceOutput  = $service();
        $outputResponse = $responder($serviceOutput);

        $outputResponse->send();
    }
}
