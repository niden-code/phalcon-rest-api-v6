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

use PDOException;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\User\User;
use Phalcon\Api\Domain\Components\DataSource\User\UserInput;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Payload;

/**
 * @phpstan-import-type TUserInput from InputTypes
 * @phpstan-import-type TValidationErrors from InputTypes
 */
final class UserPutService extends AbstractUserService
{
    protected HttpCodesEnum $errorMessage = HttpCodesEnum::AppCannotUpdateDatabaseRecord;

    /**
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        $inputObject = UserInput::new($this->sanitizer, $input);
        /** @var TValidationErrors $errors */
        $errors = $this->validator->validate($inputObject);

        /**
         * Errors exist - return early
         */
        if (true !== empty($errors)) {
            return Payload::invalid($errors);
        }

        /**
         * Check if the user exists, If not, return an error
         */
        /** @var int $userId */
        $userId     = $inputObject->id;
        $domainUser = $this->repository->user()->findById($userId);

        if (null === $domainUser) {
            return Payload::notFound();
        }

        /**
         * The password needs to be hashed
         */
        $domainUser = $this->processPassword($inputObject);

        /**
         * Update the record
         */
        try {
            $userId = $this
                ->repository
                ->user()
                ->update($domainUser)
            ;
        } catch (PDOException $ex) {
            /**
             * @todo send generic response and log the error
             */
            return $this->getErrorPayload($ex->getMessage());
        }

        if ($userId < 1) {
            return $this->getErrorPayload('No id returned');
        }

        /**
         * Get the user from the database
         */
        /** @var User $domainUser */
        $domainUser = $this->repository->user()->findById($userId);

        /**
         * Return the user back
         */
        return Payload::updated([$domainUser->id => $domainUser->toArray()]);
    }
}
