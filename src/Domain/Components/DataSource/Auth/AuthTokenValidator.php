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

namespace Phalcon\Api\Domain\Components\DataSource\Auth;

use Phalcon\Api\Domain\Components\DataSource\User\UserRepositoryInterface;
use Phalcon\Api\Domain\Components\DataSource\Validation\AbstractValidator;
use Phalcon\Api\Domain\Components\DataSource\Validation\Result;
use Phalcon\Api\Domain\Components\Encryption\TokenManagerInterface;
use Phalcon\Api\Domain\Components\Enums\Common\JWTEnum;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Enums\Input\AuthTokenInputEnum;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Filter\Validation\ValidationInterface;

final class AuthTokenValidator extends AbstractValidator
{
    protected string $fields = AuthTokenInputEnum::class;

    public function __construct(
        private TokenManagerInterface $tokenManager,
        private UserRepositoryInterface $userRepository,
        ValidationInterface $validator,
    ) {
        parent::__construct($validator);
    }

    /**
     * Validate a AuthInput and return an array of errors.
     * Empty array means valid.
     *
     * @param AuthInput $input
     *
     * @return Result
     */
    public function validate(mixed $input): Result
    {
        $errors = $this->runValidations($input);
        if (true !== empty($errors)) {
            return Result::error([HttpCodesEnum::AppTokenNotPresent->error()]);
        }

        /** @var string $token */
        $token       = $input->token;
        $tokenObject = $this->tokenManager->getObject($token);
        if (null === $tokenObject) {
            return Result::error([HttpCodesEnum::AppTokenNotValid->error()]);
        }

        if ($this->tokenIsNotRefresh($tokenObject)) {
            return Result::error([HttpCodesEnum::AppTokenNotValid->error()]);
        }

        $domainUser = $this->tokenManager->getUser($this->userRepository, $tokenObject);
        if (null === $domainUser) {
            return Result::error([HttpCodesEnum::AppTokenInvalidUser->error()]);
        }

        $errors = $this->tokenManager->validate($tokenObject, $domainUser);
        if (!empty($errors)) {
            return Result::error($errors);
        }

        $result = Result::success();
        $result->setMeta('user', $domainUser);

        return $result;
    }

    /**
     * Return if the token is a refresh one or not
     *
     * @param Token $tokenObject
     *
     * @return bool
     */
    private function tokenIsNotRefresh(Token $tokenObject): bool
    {
        $isRefresh = $tokenObject->getClaims()->get(JWTEnum::Refresh->value);

        return false === $isRefresh;
    }
}
