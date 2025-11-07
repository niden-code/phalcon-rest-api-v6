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

namespace Phalcon\Api\Domain\Services\Auth;

use Phalcon\Api\Domain\ADR\DomainInterface;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\Cache\Cache;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Env\EnvManager;

/**
 * @phpstan-import-type TValidationErrors from InputTypes
 */
abstract class AbstractAuthService implements DomainInterface
{
    public function __construct(
        protected readonly QueryRepository $repository,
        protected readonly Cache $cache,
        protected readonly EnvManager $env,
        protected readonly JWTToken $jwtToken,
        protected readonly SanitizerInterface $sanitizer,
        protected readonly Security $security,
    ) {
    }
}
