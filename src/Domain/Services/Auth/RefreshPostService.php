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
 * @phpstan-import-type TRefreshInput from InputTypes
 * @phpstan-import-type TValidationErrors from InputTypes
 */
final class RefreshPostService extends AbstractAuthService
{
    /**
     * @param TRefreshInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        /**
         * Get email and password from the input and sanitize them
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
         * @todo change this to be the domain user
         */
        $userPayload     = [
            'usr_issuer'         => $domainUser->getIssuer(),
            'usr_token_password' => $domainUser->getTokenPassword(),
            'usr_token_id'       => $domainUser->getTokenId(),
            'usr_id'             => $domainUser->getId(),
        ];
        $newToken        = $this->jwtToken->getForUser($userPayload);
        $newRefreshToken = $this->jwtToken->getRefreshForUser($userPayload);

        /**
         * Invalidate old tokens, store new tokens in cache
         */
        $this->cache->invalidateForUser($this->env, $domainUser);
        $this->cache->storeTokenInCache($this->env, $domainUser, $newToken);
        $this->cache->storeTokenInCache($this->env, $domainUser, $newRefreshToken);

        /**
         * Send the payload back
         */
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'data' => [
                    'token'        => $newToken,
                    'refreshToken' => $newRefreshToken,
                ],
            ]
        );
    }
}
