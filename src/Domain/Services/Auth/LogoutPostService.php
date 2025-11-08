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
use Phalcon\Api\Domain\Components\Payload;

/**
 * @phpstan-import-type TAuthLogoutInput from InputTypes
 */
final class LogoutPostService extends AbstractAuthService
{
    /**
     * @param TAuthLogoutInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        return $this->facade->logout($input, $this->validator);
    }
}
