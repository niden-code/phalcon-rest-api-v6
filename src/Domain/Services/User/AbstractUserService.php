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

namespace Phalcon\Api\Domain\Services\User;

use Phalcon\Api\Domain\ADR\DomainInterface;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Filter\Filter;

abstract class AbstractUserService implements DomainInterface
{
    public function __construct(
        protected readonly QueryRepository $repository,
        protected readonly TransportRepository $transport,
        protected readonly Filter $filter,
        protected readonly Security $security,
    ) {
    }
}
