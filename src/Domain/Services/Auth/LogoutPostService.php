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

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Components\Enums\Common\JWTEnum;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-import-type TLogoutInput from InputTypes
 * @phpstan-import-type TValidationErrors from InputTypes
 */
final class LogoutPostService extends AbstractAuthService
{
    /**
     * @param TLogoutInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        /**
         * @todo common code with refresh
         */
        /**
         * Get the token
         */
        $token = (string)($input['token'] ?? '');
        $token = $this->filter->string($token);

        /**
         * Validation
         *
         * Empty token
         */
        if (true === empty($token)) {
            return $this->getUnauthorizedPayload(
                [HttpCodesEnum::AppTokenNotPresent->error()]
            );
        }

        /**
         * @todo catch any exceptions here
         *
         * Is this the refresh token
         */
        $tokenObject = $this->jwtToken->getObject($token);
        $isRefresh   = $tokenObject->getClaims()->get(JWTEnum::Refresh->value);
        if (false === $isRefresh) {
            return $this->getUnauthorizedPayload(
                [HttpCodesEnum::AppTokenNotValid->error()]
            );
        }

        /**
         * Get the user - if empty return error
         */
        $user = $this
            ->jwtToken
            ->getUser($this->repository, $tokenObject)
        ;
        if (true === empty($user)) {
            return $this->getUnauthorizedPayload(
                [HttpCodesEnum::AppTokenInvalidUser->error()]
            );
        }

        $domainUser = $this->transport->newUser($user);

        /** @var TValidationErrors $errors */
        $errors = $this->jwtToken->validate($tokenObject, $domainUser);
        if (true !== empty($errors)) {
            return $this->getUnauthorizedPayload($errors);
        }

        /**
         * Invalidate old tokens
         */
        $this->cache->invalidateForUser($this->env, $domainUser);

        /**
         * Send the payload back
         */
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'data' => [
                    'authenticated' => false,
                ],
            ]
        );
    }
}
