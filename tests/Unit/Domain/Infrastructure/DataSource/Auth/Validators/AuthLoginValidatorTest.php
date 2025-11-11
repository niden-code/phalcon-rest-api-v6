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

use Faker\Factory as FakerFactory;
use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\DTO\AuthInput;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Sanitizers\AuthSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\Validators\AuthLoginValidator;
use Phalcon\Api\Domain\Infrastructure\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Filter\Validation\ValidationInterface;

final class AuthLoginValidatorTest extends AbstractUnitTestCase
{
    public function testError(): void
    {
        /** @var AuthSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::AUTH_SANITIZER);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);

        $input     = [];
        $userInput = AuthInput::new($sanitizer, $input);

        $validator = new AuthLoginValidator($validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [
            HttpCodesEnum::AppIncorrectCredentials->error(),
        ];

        $this->assertSame($expected, $actual);
    }

    public function testSuccess(): void
    {
        /** @var AuthSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::AUTH_SANITIZER);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);
        $faker     = FakerFactory::create();

        $input = [
            'email'    => $faker->safeEmail(),
            'password' => $faker->password(),
        ];
        $userInput = AuthInput::new($sanitizer, $input);

        $validator = new AuthLoginValidator($validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [];
        $this->assertSame($expected, $actual);
    }
}
