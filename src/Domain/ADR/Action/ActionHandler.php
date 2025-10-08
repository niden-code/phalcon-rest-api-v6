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

namespace Phalcon\Api\Domain\ADR\Action;

use Phalcon\Api\Domain\ADR\Domain\DomainInterface;
use Phalcon\Api\Domain\ADR\Domain\Input;
use Phalcon\Api\Domain\ADR\Responder\ResponderInterface;
use Phalcon\Api\Domain\Services\Http\Response;
use Phalcon\Http\Request;

final readonly class ActionHandler implements ActionInterface
{
    public function __construct(
        private Request $request,
        private Response $response,
        private DomainInterface $service,
        private ResponderInterface $responder
    ) {
    }

    public function __invoke(): void
    {
        $input = new Input();
        $data  = $input->__invoke($this->request);

        $this->responder->__invoke(
            $this->response,
            $this->service->__invoke($data)
        );
    }
}
