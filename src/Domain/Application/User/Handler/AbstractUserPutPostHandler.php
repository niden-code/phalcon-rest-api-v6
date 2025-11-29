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
use Phalcon\Api\Domain\Infrastructure\CommandBus\HandlerInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\Interface\MapperInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\Repository\UserRepositoryInterface;
use Phalcon\Api\Domain\Infrastructure\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Infrastructure\DataSource\Validation\ValidatorInterface;
use Phalcon\Api\Domain\Infrastructure\Encryption\Security;
use Phalcon\Api\Domain\Infrastructure\Enums\Http\HttpCodesEnum;
use Phalcon\Support\Registry;

use function array_filter;

/**
 * @phpstan-import-type TUserDomainToDbRecord from UserTypes
 * @phpstan-import-type TUserDbRecordOptional from UserTypes
 */
abstract class AbstractUserPutPostHandler implements HandlerInterface
{
    /**
     * @param ValidatorInterface      $validator
     * @param MapperInterface         $mapper
     * @param UserRepositoryInterface $repository
     * @param Security                $security
     */
    public function __construct(
        protected readonly ValidatorInterface $validator,
        protected readonly MapperInterface $mapper,
        protected readonly UserRepositoryInterface $repository,
        protected readonly Registry $registry,
        private readonly Security $security,
    ) {
    }

    /**
     * @param TUserDbRecordOptional $row
     *
     * @return TUserDbRecordOptional
     */
    protected function cleanupFields(array $row): array
    {
        unset($row['usr_id']);

        return array_filter(
            $row,
            static fn($v) => $v !== null && $v !== ''
        );
    }

    /**
     * @param HttpCodesEnum $item
     * @param string        $message
     *
     * @return Payload
     */
    protected function getErrorPayload(
        HttpCodesEnum $item,
        string $message
    ): Payload {
        return Payload::error([[$item->text() . $message]]);
    }

    /**
     * @param TUserDomainToDbRecord $input
     *
     * @return TUserDomainToDbRecord
     */
    protected function processPassword(array $input): array
    {
        if (null !== $input['usr_password']) {
            $plain  = $input['usr_password'];
            $hashed = $this->security->hash($plain);

            $input['usr_password'] = $hashed;
        }

        return $input;
    }
}
