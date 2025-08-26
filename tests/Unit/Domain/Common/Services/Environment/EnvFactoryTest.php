<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Api\Tests\Unit\Domain\Common\Services\Environment;

use Phalcon\Api\Domain\Services\Environment\Adapter\Dotenv;
use Phalcon\Api\Domain\Services\Environment\EnvFactory;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;
use Phalcon\Api\Tests\Fixtures\Domain\AbstractUnitTestCase;

final class EnvFactoryTest extends AbstractUnitTestCase
{
    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testLoad(): void
    {
        $factory = new EnvFactory();
        $dotEnv  = $factory->newInstance('dotenv');

        $class = Dotenv::class;
        $this->assertInstanceOf($class, $dotEnv);
    }

    /**
     * @return void
     * @throws InvalidConfigurationArguments
     */
    public function testUnknownService(): void
    {
        $this->expectException(InvalidConfigurationArguments::class);
        $this->expectExceptionMessage('Service unknown is not registered');

        $factory = new EnvFactory();
        $factory->newInstance('unknown');
    }
}
