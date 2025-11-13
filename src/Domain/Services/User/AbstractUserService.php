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
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Facades\UserFacade;

abstract class AbstractUserService implements DomainInterface
{
    /**
     * @param UserFacade $facade
     */
    public function __construct(
        protected readonly UserFacade $facade,
    ) {
    }
}
