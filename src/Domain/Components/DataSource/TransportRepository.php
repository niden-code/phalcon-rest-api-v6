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

namespace Phalcon\Api\Domain\Components\DataSource;

use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Encryption\Security\JWT\Token\Token;

/**
 * @phpstan-import-type TLoginResponsePayload from UserTypes
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-import-type TUserRecord from UserTypes
 *
 * Note: The 'readonly' keyword was intentionally removed from this class.
 * Properties $sessionToken and $sessionUser are mutable to support session
 * management, allowing updates to the current session state. This change
 * removes immutability guarantees, but is necessary for the intended use.
 */
final class TransportRepository
{
    private ?Token $sessionToken = null;

    private ?UserTransport $sessionUser = null;

    /**
     * Returns the session token.
     *
     * @return Token|null
     */
    public function getSessionToken(): ?Token
    {
        return $this->sessionToken;
    }

    /**
     * Returns the session user.
     *
     * @return UserTransport|null
     */
    public function getSessionUser(): ?UserTransport
    {
        return $this->sessionUser;
    }

    /**
     * @param UserTransport $user
     * @param string        $token
     * @param string        $refreshToken
     *
     * @return TLoginResponsePayload
     */
    public function newLoginUser(
        UserTransport $user,
        string $token,
        string $refreshToken
    ): array {
        return [
            'authenticated' => true,
            'user'          => [
                'id'    => $user->getId(),
                'name'  => $user->getFullName(),
                'email' => $user->getEmail(),
            ],
            'jwt'           => [
                'token'        => $token,
                'refreshToken' => $refreshToken,
            ],
        ];
    }

    /**
     * @param TUserRecord $dbUser
     *
     * @return UserTransport
     */
    public function newUser(array $dbUser): UserTransport
    {
        return new UserTransport($dbUser);
    }

    /**
     * Sets the session Token
     *
     * @param Token $token
     *
     * @return void
     */
    public function setSessionToken(Token $token): void
    {
        $this->sessionToken = $token;
    }

    /**
     * Populates the session user with the user data.
     *
     * @param TUserDbRecord $user
     *
     * @return void
     */
    public function setSessionUser(array $user): void
    {
        $this->sessionUser = $this->newUser($user);
    }
}
