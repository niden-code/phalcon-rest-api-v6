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

namespace Phalcon\Api\Tests\Unit\Domain\Components\DataSource\User;

use Faker\Factory as FakerFactory;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\User\UserInput;
use Phalcon\Api\Domain\Components\DataSource\User\UserSanitizer;
use Phalcon\Api\Domain\Components\DataSource\User\UserValidator;
use Phalcon\Api\Tests\AbstractUnitTestCase;

final class UserValidatorTest extends AbstractUnitTestCase
{
    public function testError(): void
    {
        /** @var UserSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::USER_SANITIZER);

        $input     = [];
        $userInput = UserInput::new($sanitizer, $input);

        $validator = new UserValidator();
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [
            ['Field email cannot be empty.'],
            ['Field password cannot be empty.'],
            ['Field issuer cannot be empty.'],
            ['Field tokenPassword cannot be empty.'],
            ['Field tokenId cannot be empty.'],
        ];

        $this->assertSame($expected, $actual);
    }

    public function testSuccess(): void
    {
        /** @var UserSanitizer $sanitizer */
        $sanitizer = $this->container->get(Container::USER_SANITIZER);
        $faker     = FakerFactory::create();

        $input = [
            'email'         => $faker->safeEmail(),
            'password'      => $faker->password(),
            'issuer'        => $faker->company(),
            'tokenPassword' => $faker->password(),
            'tokenId'       => $faker->uuid(),
        ];

        $userInput = UserInput::new($sanitizer, $input);

        $validator = new UserValidator();
        $result    = $validator->validate($userInput);
        $actual    = $result->getErrors();

        $expected = [];
        $this->assertSame($expected, $actual);
    }
}
