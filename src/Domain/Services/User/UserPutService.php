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
use PDOException;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Domain\Payload;

/**
 * @phpstan-import-type TUserSanitizedUpdateInput from InputTypes
 * @phpstan-import-type TUserInput from InputTypes
 * @phpstan-import-type TValidationErrors from InputTypes
 */
final class UserPutService extends AbstractUserService
{
    /**
     * @param TUserInput $input
     *
     * @return Payload
     */
    public function __invoke(array $input): Payload
    {
        $inputData = $this->sanitizeInput($input);
        $errors    = $this->validateInput($inputData);

        /**
         * Errors exist - return early
         */
        if (true !== empty($errors)) {
            return new Payload(
                DomainStatus::INVALID,
                [
                    'errors' => $errors,
                ]
            );
        }

        /**
         * The password needs to be hashed
         */
        $password = $inputData['password'];
        $hashed   = $this->security->hash($password);

        $inputData['password'] = $hashed;

        /**
         * Update the record
         */
        /**
         * @todo get the user from the database to make sure that it is valid
         */

        try {
            $userId = $this
                ->repository
                ->user()
                ->update($inputData)
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
        $dbUser     = $this->repository->user()->findById($userId);
        $domainUser = $this->transport->newUser($dbUser);

        /**
         * Return the user back
         */
        return new Payload(
            DomainStatus::UPDATED,
            [
                'data' => $domainUser->toArray(),
            ]
        );
    }

    /**
     * @param string $message
     *
     * @return Payload
     */
    private function getErrorPayload(string $message): Payload
    {
        return new Payload(
            DomainStatus::ERROR,
            [
                'errors' => [
                    HttpCodesEnum::AppCannotUpdateDatabaseRecord->text()
                    . $message,
                ],
            ]
        );
    }

    /**
     * @param TUserInput $input
     *
     * @return TUserSanitizedUpdateInput
     */
    private function sanitizeInput(array $input): array
    {
        /**
         * Only the fields we want
         *
         * @todo add sanitizers here
         * @todo maybe this is another domain object?
         */
        $sanitized = [
            'id'            => $input['id'] ?? 0,
            'status'        => $input['status'] ?? 0,
            'email'         => $input['email'] ?? '',
            'password'      => $input['password'] ?? '',
            'namePrefix'    => $input['namePrefix'] ?? '',
            'nameFirst'     => $input['nameFirst'] ?? '',
            'nameLast'      => $input['nameLast'] ?? '',
            'nameMiddle'    => $input['nameMiddle'] ?? '',
            'nameSuffix'    => $input['nameSuffix'] ?? '',
            'issuer'        => $input['issuer'] ?? '',
            'tokenPassword' => $input['tokenPassword'] ?? '',
            'tokenId'       => $input['tokenId'] ?? '',
            'preferences'   => $input['preferences'] ?? '',
            'updatedDate'   => $input['updatedDate'] ?? null,
            'updatedUserId' => $input['updatedUserId'] ?? 0,
        ];

        if (empty($sanitized['updatedDate'])) {
            $sanitized['updatedDate'] = Dates::toUTC('now', Dates::DATE_TIME_FORMAT);
        }

        return $sanitized;
    }

    /**
     * @param TUserSanitizedUpdateInput $inputData
     *
     * @return TValidationErrors|array{}
     */
    private function validateInput(array $inputData): array
    {
        $errors   = [];
        $required = [
            'email',
            'password',
            'issuer',
            'tokenPassword',
            'tokenId',
        ];

        foreach ($required as $name) {
            $field = $inputData[$name];
            if (true === empty($field)) {
                $errors[] = ['Field ' . $name . ' cannot be empty.'];
            }
        }

        return $errors;
    }
}
