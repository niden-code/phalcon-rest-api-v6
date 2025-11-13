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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Facades;

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\ADR\Payload;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\DTO\AuthInput;
use Phalcon\Api\Domain\Infrastructure\DataSource\Interfaces\SanitizerInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\DTO\User;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repositories\UserRepositoryInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\Validation\ValidatorInterface;
use Phalcon\Api\Domain\Infrastructure\Encryption\Security;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenManagerInterface;
use Phalcon\Api\Domain\Infrastructure\Enums\Http\HttpCodesEnum;

/**
 * @phpstan-import-type TAuthLoginInput from InputTypes
 * @phpstan-import-type TAuthLogoutInput from InputTypes
 * @phpstan-import-type TAuthRefreshInput from InputTypes
 */
final class AuthFacade
{
    /**
     * @param UserRepositoryInterface $repository
     * @param SanitizerInterface      $sanitizer
     * @param TokenManagerInterface   $tokenManager
     * @param Security                $security
     */
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly SanitizerInterface $sanitizer,
        private readonly TokenManagerInterface $tokenManager,
        private readonly Security $security,
    ) {
    }

    /**
     * Authenticates users (login)
     *
     * @param TAuthLoginInput    $input
     * @param ValidatorInterface $validator
     *
     * @return Payload
     */
    public function authenticate(
        array $input,
        ValidatorInterface $validator
    ): Payload {
        /**
         * Data Transfer Object
         */
        $dto = AuthInput::new($this->sanitizer, $input);

        /**
         * Validate
         */
        $validation = $validator->validate($dto);
        if (!$validation->isValid()) {
            return Payload::unauthorized($validation->getErrors());
        }

        /**
         * Find the user by email
         */
        /** @var string $email */
        $email      = $dto->email;
        $domainUser = $this->repository->findByEmail($email);
        if (null === $domainUser) {
            return Payload::unauthorized([HttpCodesEnum::AppIncorrectCredentials->error()]);
        }

        /**
         * Verify the password
         */
        /** @var string $suppliedPassword */
        $suppliedPassword = $dto->password;
        /** @var string $dbPassword */
        $dbPassword = $domainUser->password;
        if (true !== $this->security->verify($suppliedPassword, $dbPassword)) {
            return Payload::unauthorized([HttpCodesEnum::AppIncorrectCredentials->error()]);
        }

        /**
         * Issue a new set of tokens
         */
        $tokens = $this->tokenManager->issue($domainUser);

        /**
         * Construct the response
         */
        $results = [
            'authenticated' => true,
            'user'          => [
                'id'    => $domainUser->id,
                'name'  => $domainUser->fullName(),
                'email' => $domainUser->email,
            ],
            'jwt'           => [
                'token'        => $tokens['token'],
                'refreshToken' => $tokens['refreshToken'],
            ],
        ];

        return Payload::success($results);
    }

    /**
     * Logout: revoke refresh token after parsing/validation.
     *
     * @param TAuthLogoutInput   $input
     * @param ValidatorInterface $validator
     *
     * @return Payload
     */
    public function logout(
        array $input,
        ValidatorInterface $validator
    ): Payload {
        /**
         * Data Transfer Object
         */
        $dto = AuthInput::new($this->sanitizer, $input);

        /**
         * Validate
         */
        $validation = $validator->validate($dto);
        if (!$validation->isValid()) {
            return Payload::unauthorized($validation->getErrors());
        }

        /**
         * If we are here validation has passed and the Result object
         * has the user in the meta store
         */
        /** @var User $domainUser */
        $domainUser = $validation->getMeta('user');

        $this->tokenManager->revoke($domainUser);

        return Payload::success(['authenticated' => false]);
    }

    /**
     * Refresh: validate refresh token, issue new tokens via TokenManager.
     *
     * @param TAuthLogoutInput   $input
     * @param ValidatorInterface $validator
     *
     * @return Payload
     */
    public function refresh(
        array $input,
        ValidatorInterface $validator
    ): Payload {
        /**
         * Data Transfer Object
         */
        $dto = AuthInput::new($this->sanitizer, $input);

        /**
         * Validate
         */
        $validation = $validator->validate($dto);
        if (!$validation->isValid()) {
            return Payload::unauthorized($validation->getErrors());
        }

        /**
         * If we are here validation has passed and the Result object
         * has the user in the meta store
         */
        /** @var User $domainUser */
        $domainUser = $validation->getMeta('user');

        $tokens = $this->tokenManager->refresh($domainUser);

        return Payload::success([
            'token'        => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
        ]);
    }
}
