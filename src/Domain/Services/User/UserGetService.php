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

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type TUserInput from InputTypes
 */
final class UserGetService extends AbstractUserService
{
    /**
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        $userId = $this->filter->absint($input['id'] ?? 0);

        /**
         * Success
         */
        if ($userId > 0) {
            $dbUser = $this->repository->user()->findById($userId);
            $user   = $this->transport->newUser($dbUser);

            if (true !== $user->isEmpty()) {
                return new Payload(
                    DomainStatus::SUCCESS,
                    [
                        'data' => $user->toArray(),
                    ]
                );
            }
        }

        /**
         * 404
         */
        return new Payload(
            DomainStatus::NOT_FOUND,
            [
                'errors' => [
                    'Record(s) not found',
                ],
            ]
        );
    }
}
