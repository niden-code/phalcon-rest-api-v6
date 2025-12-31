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

namespace Phalcon\Api\Domain\Application\Traits;

use Phalcon\Api\Domain\ADR\Payload;
use Phalcon\Api\Domain\Application\Company\Command\CompanyDeleteCommand;
use Phalcon\Api\Domain\Application\Company\Command\CompanyGetCommand;
use Phalcon\Api\Domain\Application\User\Command\UserDeleteCommand;
use Phalcon\Api\Domain\Application\User\Command\UserGetCommand;
use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\Company\DTO\Company;
use Phalcon\Api\Domain\Infrastructure\DataSource\Company\Repository\CompanyRepository;
use Phalcon\Api\Domain\Infrastructure\DataSource\Transformer\Transformer;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\DTO\User;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repository\UserRepository;

/**
 * @phpstan-type TCommand UserDeleteCommand|CompanyDeleteCommand
 * @phpstan-type TRepository UserRepository|CompanyRepository
 *
 * @property TRepository                            $repository
 * @property Transformer<User>|Transformer<Company> $transformer
 */
trait DeleteHandlerTrait
{
    /**
     * Delete a user.
     *
     * @param CommandInterface $command
     *
     * @return Payload
     */
    public function __invoke(CommandInterface $command): Payload
    {
        /** @var TCommand $command */
        $recordId = $command->id;

        /**
         * Success
         */
        if ($recordId > 0) {
            $rowCount = $this->repository->deleteById($recordId);

            if (0 !== $rowCount) {
                return Payload::deleted($this->transformer->delete($recordId));
            }
        }

        /**
         * 404
         */
        return Payload::notFound();
    }
}
