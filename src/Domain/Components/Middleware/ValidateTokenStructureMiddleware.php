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

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Domain\Components\Exceptions\TokenValidationException;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Exception;
use Phalcon\Mvc\Micro;
use Phalcon\Support\Registry;

final class ValidateTokenStructureMiddleware extends AbstractMiddleware
{
    /**
     * @param Micro $application
     *
     * @return bool
     * @throws EventsException
     * @throws Exception
     */
    public function call(Micro $application): bool
    {
        /** @var Request $request */
        $request = $application->getSharedService(Container::REQUEST);
        /** @var EnvManager $env */
        $env = $application->getSharedService(Container::ENV);
        /** @var JWTToken $jwtToken */
        $jwtToken = $application->getSharedService(Container::JWT_TOKEN);

        try {
            $token = $jwtToken->getObject(
                $this->getBearerTokenFromHeader($request, $env)
            );
        } catch (TokenValidationException $ex) {
            $this->halt(
                $application,
                HttpCodesEnum::Unauthorized->value,
                HttpCodesEnum::Unauthorized->text(),
                [],
                [
                    [$ex->getCode() => $ex->getMessage()],
                ]
            );

            return false;
        }

        /**
         * If we are down here the token is an object and is valid
         */
        /** @var Registry $registry */
        $registry = $application->getSharedService(Container::REGISTRY);
        $registry->set('token', $token);

        return true;
    }
}
