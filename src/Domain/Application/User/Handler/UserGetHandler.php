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

namespace Phalcon\Api\Domain\Application\User\Handler;

use Phalcon\Api\Domain\ADR\Payload;
use Phalcon\Api\Domain\Application\User\Command\UserGetCommand;
use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandInterface;
use Phalcon\Api\Domain\Infrastructure\CommandBus\HandlerInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repository\UserRepositoryInterface;

final class UserGetHandler implements HandlerInterface
{
    /**
     * @param UserRepositoryInterface $repository
     */
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    /**
     * Get a user.
     *
     * @param CommandInterface $command
     *
     * @return Payload
     */
    public function __invoke(CommandInterface $command): Payload
    {
        /** @var UserGetCommand $command */
        $userId = $command->id;

        /**
         * Success
         */
        if ($userId > 0) {
            $user = $this->repository->findById($userId);

            if (null !== $user) {
                return Payload::success([$user->id => $user->toArray()]);
            }
        }

        /**
         * 404
         */
        return Payload::notFound();
    }
}
