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

namespace Phalcon\Api\Domain\Services;

use Phalcon\Api\Domain\Interfaces\ActionInterface;
use Phalcon\Api\Domain\Interfaces\DomainInterface;
use Phalcon\Api\Domain\Interfaces\ResponderInterface;

final readonly class ActionHandler implements ActionInterface
{
    public function __construct(
        private DomainInterface $service,
        private ResponderInterface $responder
    ) {
    }

    public function __invoke(): void
    {
        $this->responder->__invoke(
            $this->service->__invoke()
        );
    }
}
