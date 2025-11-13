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

namespace Phalcon\Api\Tests\Unit\Domain\Infrastructure\DataSource\User\Validators;

use Faker\Factory as FakerFactory;
use Phalcon\Api\Domain\Infrastructure\Container;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\DTO\UserInput;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Sanitizers\UserSanitizer;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Validators\UserValidator;
use Phalcon\Api\Tests\AbstractUnitTestCase;
use Phalcon\Filter\Validation\ValidationInterface;

final class UserValidatorTest extends AbstractUnitTestCase
{
    public function testError(): void
    {
        /** @var UserSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::USER_SANITIZER);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);

        $input     = [];
        $userInput = UserInput::new($sanitizer, $input);

        $validator = new UserValidator($validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [
            ['Field email is required'],
            ['Field email must be an email address'],
            ['Field password is required'],
            ['Field issuer is required'],
            ['Field tokenPassword is required'],
            ['Field tokenId is required'],
        ];

        $this->assertSame($expected, $actual);
    }

    public function testSuccess(): void
    {
        /** @var UserSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::USER_SANITIZER);
        /** @var ValidationInterface $validation */
        $validation = $this->container->get(Container::VALIDATION);
        $faker     = FakerFactory::create();

        $input = [
            'email'         => $faker->safeEmail(),
            'password'      => $faker->password(),
            'issuer'        => $faker->company(),
            'tokenPassword' => $faker->password(),
            'tokenId'       => $faker->uuid(),
        ];

        $userInput = UserInput::new($sanitizer, $input);

        $validator = new UserValidator($validation);
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [];
        $this->assertSame($expected, $actual);
    }
}
