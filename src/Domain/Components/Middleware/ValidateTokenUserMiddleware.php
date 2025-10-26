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
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Components\Encryption\JWTToken;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Http\Response\Exception;
use Phalcon\Mvc\Micro;

/**
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-import-type TUserRecord from UserTypes
 */
final class ValidateTokenUserMiddleware extends AbstractMiddleware
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
        /** @var JWTToken $jwtToken */
        $jwtToken = $application->getSharedService(Container::JWT_TOKEN);
        /** @var QueryRepository $repository */
        $repository = $application->getSharedService(Container::REPOSITORY);
        /** @var TransportRepository $transport */
        $transport = $application->getSharedService(Container::REPOSITORY_TRANSPORT);

        /**
         * Get the token object
         */
        /** @var Token $tokenObject */
        $tokenObject = $transport->getSessionToken();
        /** @var TUserRecord $dbUser */
        $dbUser      = $jwtToken->getUser($repository, $tokenObject);

        if (true === empty($dbUser)) {
            $this->halt(
                $application,
                HttpCodesEnum::Unauthorized->value,
                HttpCodesEnum::Unauthorized->text(),
                [],
                [HttpCodesEnum::AppTokenInvalidUser->error()]
            );

            return false;
        }

        /**
         * If we are here everything is fine and we need to keep the user
         * as a "session" user in the transport
         */
        /** @var TUserDbRecord $dbUser */
        $transport->setSessionUser($dbUser);

        return true;
    }
}
