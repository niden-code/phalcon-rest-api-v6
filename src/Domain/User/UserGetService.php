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

namespace Phalcon\Api\Domain\User;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\Domain\DomainInterface;
use Phalcon\Api\Domain\ADR\Domain\InputTypes;
use Phalcon\Api\Domain\DataSource\User\UserRepository;
use Phalcon\Domain\Payload;

use function abs;

/**
 * @phpstan-import-type TUserInput from InputTypes
 */
final readonly class UserGetService implements DomainInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        $userId = abs((int)($input['userId'] ?? 0));

        /**
         * Success
         */
        if ($userId > 0) {
            $user = $this->userRepository->findById($userId);

            if (true !== $user->isEmpty()) {
                return new Payload(
                    DomainStatus::SUCCESS,
                    [
                        'results' => $user->toArray(),
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
                'results' => [
                    'Record(s) not found',
                ],
            ]
        );
    }
}
