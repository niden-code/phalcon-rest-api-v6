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

namespace Phalcon\Api\Domain\Components\DataSource\User;

use PDOException;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\DataSource\Interfaces\MapperInterface;
use Phalcon\Api\Domain\Components\DataSource\Interfaces\SanitizerInterface;
use Phalcon\Api\Domain\Components\DataSource\Validation\ValidatorInterface;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Payload;

use function array_filter;

/**
 * Orchestration for workflow
 *
 * - Sanitization
 * - DTO creation
 * - Validation
 * - Pre-operation checks (when necessary)
 * - Repository operation
 *
 * @phpstan-import-type TUserInput from InputTypes
 * @phpstan-import-type TUserDomainToDbRecord from UserTypes
 * @phpstan-import-type TUserDbRecordOptional from UserTypes
 */
final class UserFacade
{
    /**
     * @param SanitizerInterface      $sanitizer
     * @param ValidatorInterface      $validator
     * @param MapperInterface         $mapper
     * @param UserRepositoryInterface $repository
     * @param Security                $security
     */
    public function __construct(
        private readonly SanitizerInterface $sanitizer,
        private readonly ValidatorInterface $validator,
        private readonly MapperInterface $mapper,
        private readonly UserRepositoryInterface $repository,
        private readonly Security $security,
    ) {
    }

    /**
     * Delete a user.
     *
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function delete(array $input): Payload
    {
        $dto    = UserInput::new($this->sanitizer, $input);
        $userId = $dto->id;

        /**
         * Success
         */
        if ($userId > 0) {
            $rowCount = $this->repository->deleteById($userId);

            if (0 !== $rowCount) {
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

    /**
     * Get a user.
     *
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function get(array $input): Payload
    {
        $dto    = UserInput::new($this->sanitizer, $input);
        $userId = $dto->id;

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

    /**
     * Create a user.
     *
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function insert(array $input): Payload
    {
        $dto = UserInput::new($this->sanitizer, $input);

        $validation = $this->validator->validate($dto);
        if (!$validation->isValid()) {
            return Payload::invalid($validation->getErrors());
        }

        /**
         * Array for inserting
         */
        $user = $this->mapper->db($dto);

        /**
         * Pre-insert checks and manipulations
         */
        $user = $this->preInsert($user);

        /**
         * Insert the record
         */
        try {
            $userId = $this->repository->insert($user);
        } catch (PDOException $ex) {
            /**
             * @todo send generic response and log the error
             */
            return $this->getErrorPayload(
                HttpCodesEnum::AppCannotCreateDatabaseRecord,
                $ex->getMessage()
            );
        }

        if ($userId < 1) {
            return $this->getErrorPayload(
                HttpCodesEnum::AppCannotCreateDatabaseRecord,
                'No id returned'
            );
        }

        /**
         * Get the user from the database
         */
        /** @var User $domainUser */
        $domainUser = $this->repository->findById($userId);

        /**
         * Return the user back
         */
        return Payload::created([$domainUser->id => $domainUser->toArray()]);
    }

    /**
     * Create a user.
     *
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function update(array $input): Payload
    {
        $dto = UserInput::new($this->sanitizer, $input);

        $validation = $this->validator->validate($dto);
        if (!$validation->isValid()) {
            return Payload::invalid($validation->getErrors());
        }

        /**
         * Check if the user exists, If not, return an error
         */
        /** @var int $userId */
        $userId     = $dto->id;
        $domainUser = $this->repository->findById($userId);

        if (null === $domainUser) {
            return Payload::notFound();
        }

        /**
         * Array for updating
         */
        $user = $this->mapper->db($dto);

        /**
         * Pre-update checks and manipulations
         */
        $user = $this->preUpdate($user);

        /**
         * Update the record
         */
        try {
            $userId = $this->repository->update($userId, $user);
        } catch (PDOException $ex) {
            /**
             * @todo send generic response and log the error
             */
            return $this->getErrorPayload(
                HttpCodesEnum::AppCannotUpdateDatabaseRecord,
                $ex->getMessage()
            );
        }

        if ($userId < 1) {
            return $this->getErrorPayload(
                HttpCodesEnum::AppCannotUpdateDatabaseRecord,
                'No id returned'
            );
        }

        /**
         * Get the user from the database
         */
        /** @var User $domainUser */
        $domainUser = $this->repository->findById($userId);

        /**
         * Return the user back
         */
        return Payload::updated([$domainUser->id => $domainUser->toArray()]);
    }

    /**
     * @param string $message
     *
     * @return Payload
     */
    private function getErrorPayload(
        HttpCodesEnum $item,
        string $message): Payload
    {
        return Payload::error([[$item->text() . $message]]);
    }

    /**
     * @param TUserDomainToDbRecord $input
     *
     * @return TUserDbRecordOptional
     */
    private function preInsert(array $input): array
    {
        $result = $this->processPassword($input);
        $now    = Dates::toUTC(format: Dates::DATE_TIME_FORMAT);

        /**
         * Set the created/updated dates if need be
         */
        if (true === empty($result['usr_created_date'])) {
            $result['usr_created_date'] = $now;
        }
        if (true === empty($result['usr_updated_date'])) {
            $result['usr_updated_date'] = $now;
        }

        /** @var TUserDbRecordOptional $result */
        return $this->cleanupFields($result);
    }

    /**
     * @param TUserDomainToDbRecord $input
     *
     * @return TUserDbRecordOptional
     */
    private function preUpdate(array $input): array
    {
        $result = $this->processPassword($input);
        $now    = Dates::toUTC(format: Dates::DATE_TIME_FORMAT);

        /**
         * Set updated date to now if it has not been set
         */
        if (true === empty($result['usr_updated_date'])) {
            $result['usr_updated_date'] = $now;
        }

        /**
         * Remove createdDate and createdUserId - cannot be changed. This
         * needs to be here because we don't want to touch those fields.
         */
        unset($result['usr_created_date'], $result['usr_created_usr_id']);


        return $this->cleanupFields($result);
    }

    /**
     * @param TUserDomainToDbRecord $input
     *
     * @return TUserDomainToDbRecord
     */
    private function processPassword(array $input): array
    {
        if (null !== $input['usr_password']) {
            $plain  = $input['usr_password'];
            $hashed = $this->security->hash($plain);

            $input['usr_password'] = $hashed;
        }

        return $input;
    }

    /**
     * @param TUserDbRecordOptional $row
     *
     * @return TUserDbRecordOptional
     */
    private function cleanupFields(array $row): array
    {
        unset($row['usr_id']);

        return array_filter(
            $row,
            static fn($v) => $v !== null && $v !== ''
        );
    }
}
