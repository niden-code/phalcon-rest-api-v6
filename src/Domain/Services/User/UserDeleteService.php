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

namespace Phalcon\Api\Domain\Services\User;

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\User\UserInput;
use Phalcon\Api\Domain\Components\Payload;

/**
 * @phpstan-import-type TUserInput from InputTypes
 */
final class UserDeleteService extends AbstractUserService
{
    /**
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        $inputObject = UserInput::new($this->sanitizer, $input);
        $userId      = $inputObject->id;

        /**
         * Success
         */
        if ($userId > 0) {
            $rows = $this->repository->user()->deleteById($userId);

            if ($rows > 0) {
                return Payload::deleted(
                    [
                        'Record deleted successfully [#' . $userId . '].',
                    ],
                );
            }
        }

        /**
         * 404
         */
        return Payload::notFound();
    }
}
