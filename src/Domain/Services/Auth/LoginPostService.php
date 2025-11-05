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
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-import-type TLoginInput from InputTypes
 */
final class LoginPostService extends AbstractAuthService
{
    /**
     * @param TLoginInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        /**
         * Get email and password from the input and sanitize them
         */
        $email    = (string)($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $email    = $this->filter->string($email);
        $password = $this->filter->string($password);

        /**
         * Check if email or password are empty
         */
        if (true === empty($email) || true === empty($password)) {
            return $this->getUnauthorizedPayload(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        /**
         * Find the user in the database
         */
        $dbUser     = $this->repository->user()->findByEmail($email);
        $dbUserId   = (int)($dbUser['usr_id'] ?? 0);
        $dbPassword = $dbUser['usr_password'] ?? '';

        /**
         * Check if the user exists and if the password matches
         */
        if (
            $dbUserId < 1 ||
            true !== $this->security->verify($password, $dbPassword)
        ) {
            return $this->getUnauthorizedPayload(
                [HttpCodesEnum::AppIncorrectCredentials->error()]
            );
        }

        /**
         * Get a new token for this user
         */
        /** @var TUserDbRecord $dbUser */
        $token        = $this->jwtToken->getForUser($dbUser);
        $refreshToken = $this->jwtToken->getRefreshForUser($dbUser);
        $domainUser   = $this->transport->newUser($dbUser);
        $results      = $this->transport->newLoginUser(
            $domainUser,
            $token,
            $refreshToken
        );

        /**
         * Store the token in cache
         */
        $this->cache->storeTokenInCache($this->env, $domainUser, $token);
        $this->cache->storeTokenInCache($this->env, $domainUser, $refreshToken);

        /**
         * Send the payload back
         */
        return new Payload(
            DomainStatus::SUCCESS,
            [
                'data' => $results,
            ]
        );
    }
}
