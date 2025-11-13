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

namespace Phalcon\Api\Tests\Unit\Domain\Infrastructure\DataSource\Auth\Validators;

use Exception;
use Faker\Factory;
use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\DTO\AuthInput;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Sanitizers\AuthSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validators\AuthTokenValidator;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repositories\UserRepository;
use Phalcon\Api\Domain\Infrastructure\Encryption\JWTToken;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenCacheInterface;
use Phalcon\Api\Domain\Infrastructure\Encryption\TokenManager;
use Phalcon\Api\Domain\Infrastructure\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Infrastructure\Env\EnvManager;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Filter\Validation\ValidationInterface;

final class AuthTokenValidatorTest extends AbstractUnitTestCase
{
    public function testFailureTokenNotPresent(): void
    {
        /** @var AuthSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::AUTH_SANITIZER);
        /** @var TokenManager $tokenManager */
        $tokenManager = $this->container->get(Container::JWT_TOKEN_MANAGER);
        /** @var UserRepository $repository */
        $repository = $this->container->get(Container::USER_REPOSITORY);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);

        $input     = [];
        $userInput = AuthInput::new($sanitizer, $input);

        $validator = new AuthTokenValidator($tokenManager, $repository, $validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [
            HttpCodesEnum::AppTokenNotPresent->error(),
        ];

        $this->assertSame($expected, $actual);
    }

    public function testFailureTokenNotValid(): void
    {
        /** @var EnvManager $env */
        $env = $this->container->get(Container::ENV);
        /** @var TokenCacheInterface $tokenCache */
        $tokenCache = $this->container->get(Container::JWT_TOKEN_CACHE);
        /** @var AuthSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::AUTH_SANITIZER);
        /** @var UserRepository $repository */
        $repository = $this->container->get(Container::USER_REPOSITORY);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);

        $mockJWTToken = $this
            ->getMockBuilder(JWTToken::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getObject'
                ]
            )
            ->getMock()
        ;
        $mockJWTToken
            ->method('getObject')
            ->willThrowException(new Exception('error'))
        ;

        $userData = $this->getNewUserData();
        $userData['usr_id'] = rand(1, 100);
        $token = $this->getUserToken($userData);

        $tokenManager = new TokenManager($tokenCache, $env, $mockJWTToken);

        $input     = [
            'token' => $token,
        ];
        $userInput = AuthInput::new($sanitizer, $input);

        $validator = new AuthTokenValidator($tokenManager, $repository, $validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [
            HttpCodesEnum::AppTokenNotValid->error(),
        ];

        $this->assertSame($expected, $actual);
    }
}
