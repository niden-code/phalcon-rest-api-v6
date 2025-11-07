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

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\Auth\AuthInput;
use Phalcon\Api\Domain\Components\Enums\Common\JWTEnum;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Payload;

/**
 * @phpstan-import-type TAuthRefreshInput from InputTypes
 * @phpstan-import-type TValidationErrors from InputTypes
 */
final class RefreshPostService extends AbstractAuthService
{
    /**
     * @param TAuthRefreshInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        /**
         * Get email and password from the input and sanitize them
         */
        $inputObject = AuthInput::new($this->sanitizer, $input);
        $token       = $inputObject->token;

        /**
         * Validation
         *
         * Empty token
         */
        if (true === empty($token)) {
            return Payload::unauthorized(
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
            return Payload::unauthorized(
                [HttpCodesEnum::AppTokenNotValid->error()]
            );
        }

        /**
         * Get the user - if empty return error
         */
        $domainUser = $this
            ->jwtToken
            ->getUser($this->repository, $tokenObject)
        ;
        if (null === $domainUser) {
            return Payload::unauthorized(
                [HttpCodesEnum::AppTokenInvalidUser->error()]
            );
        }

        /** @var TValidationErrors $errors */
        $errors = $this->jwtToken->validate($tokenObject, $domainUser);
        if (true !== empty($errors)) {
            return Payload::unauthorized($errors);
        }

        $newToken        = $this->jwtToken->getForUser($domainUser);
        $newRefreshToken = $this->jwtToken->getRefreshForUser($domainUser);

        /**
         * Invalidate old tokens, store new tokens in cache
         */
        $this->cache->invalidateForUser($this->env, $domainUser);
        $this->cache->storeTokenInCache($this->env, $domainUser, $newToken);
        $this->cache->storeTokenInCache($this->env, $domainUser, $newRefreshToken);

        /**
         * Send the payload back
         */
        return Payload::success(
            [
                'token'        => $newToken,
                'refreshToken' => $newRefreshToken,
            ],
        );
    }
}
