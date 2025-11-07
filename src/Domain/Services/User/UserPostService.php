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
final class UserPostService extends AbstractUserService
{
    protected HttpCodesEnum $errorMessage = HttpCodesEnum::AppCannotCreateDatabaseRecord;

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
         * The password needs to be hashed
         */
        $domainUser = $this->processPassword($inputObject);

        /**
         * Insert the record
         */
        try {
            $userId = $this
                ->repository
                ->user()
                ->insert($domainUser)
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
        return Payload::created([$domainUser->id => $domainUser->toArray()]);
    }
}
