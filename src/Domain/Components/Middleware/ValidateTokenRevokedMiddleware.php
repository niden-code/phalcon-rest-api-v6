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

namespace Phalcon\Api\Domain\Components\Middleware;

use Phalcon\Api\Domain\Components\Constants\Cache as CacheConstants;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\User\User;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Micro;
use Phalcon\Support\Registry;
use Psr\SimpleCache\CacheInterface;

final class ValidateTokenRevokedMiddleware extends AbstractMiddleware
{
    /**
     * @param Micro $application
     *
     * @return bool
     */
    public function call(Micro $application): bool
    {
        /** @var RequestInterface $request */
        $request = $application->getSharedService(Container::REQUEST);
        /** @var CacheInterface $cache */
        $cache = $application->getSharedService(Container::CACHE);
        /** @var EnvManager $env */
        $env = $application->getSharedService(Container::ENV);
        /** @var Registry $registry */
        $registry = $application->getSharedService(Container::REGISTRY);

        /** @var User $domainUser */
        $domainUser = $registry->get('user');

        /**
         * Get the token object
         */
        $token = $this->getBearerTokenFromHeader($request, $env);
        $cacheKey = CacheConstants::getCacheTokenKey($domainUser, $token);
        $exists = $cache->has($cacheKey);

        if (true !== $exists) {
            $this->halt(
                $application,
                HttpCodesEnum::Unauthorized->value,
                HttpCodesEnum::Unauthorized->text(),
                [],
                [HttpCodesEnum::AppTokenNotValid->error()]
            );

            return false;
        }

        return true;
    }
}
