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
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Payload;

/**
 * @phpstan-import-type TAuthLoginInput from InputTypes
 */
final class LoginPostService extends AbstractAuthService
{
    /**
     * @param TAuthLoginInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        /**
         * Get email and password from the input and sanitize them
         */
        $inputObject = AuthInput::new($this->sanitizer, $input);
        $email       = $inputObject->email;
        $password    = $inputObject->password;

        /**
         * Check if email or password are empty
         */
        if (true === empty($email) || true === empty($password)) {
            return Payload::unauthorized(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        /**
         * Find the user in the database
         */
        $domainUser = $this->repository->user()->findByEmail($email);

        /**
         * Check if the user exists
         */
        if (null === $domainUser) {
            return Payload::unauthorized(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        /**
         * Check if the password matches
         */
        if (true !== $this->security->verify($password, $domainUser->password)) {
            return Payload::unauthorized(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        /**
         * Get a new token for this user
         */
        $token        = $this->jwtToken->getForUser($domainUser);
        $refreshToken = $this->jwtToken->getRefreshForUser($domainUser);

        /**
         * Store the token in cache
         */
        $this->cache->storeTokenInCache($this->env, $domainUser, $token);
        $this->cache->storeTokenInCache($this->env, $domainUser, $refreshToken);

        /**
         * Send the payload back
         */
        $results = [
            'authenticated' => true,
            'user'          => [
                'id'    => $domainUser->id,
                'name'  => $domainUser->fullName(),
                'email' => $domainUser->email,
            ],
            'jwt'           => [
                'token'        => $token,
                'refreshToken' => $refreshToken,
            ],
        ];

        return Payload::success($results);
    }
}
