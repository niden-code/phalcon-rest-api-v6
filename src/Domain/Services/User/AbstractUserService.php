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

use Phalcon\Api\Domain\ADR\DomainInterface;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;
use Phalcon\Api\Domain\Components\DataSource\User\User;
use Phalcon\Api\Domain\Components\DataSource\User\UserInput;
use Phalcon\Api\Domain\Components\DataSource\User\UserMapper;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Components\DataSource\User\UserValidator;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Enums\Http\HttpCodesEnum;
use Phalcon\Api\Domain\Components\Payload;

use function array_shift;

/**
 * @phpstan-import-type TUser from UserTypes
 */
abstract class AbstractUserService implements DomainInterface
{
    protected HttpCodesEnum $errorMessage;

    /**
     * @param QueryRepository    $repository
     * @param UserMapper         $mapper
     * @param UserValidator      $validator
     * @param SanitizerInterface $sanitizer
     * @param Security           $security
     */
    public function __construct(
        protected readonly QueryRepository $repository,
        protected readonly UserMapper $mapper,
        protected readonly UserValidator $validator,
        protected readonly SanitizerInterface $sanitizer,
        protected readonly Security $security,

    ) {
    }

    /**
     * @param string $message
     *
     * @return Payload
     */
    protected function getErrorPayload(string $message): Payload
    {
        return Payload::error(
            [
                [$this->errorMessage->text() . $message],
            ]
        );
    }

    /**
     * @param UserInput $inputObject
     *
     * @return User
     */
    protected function processPassword(UserInput $inputObject): User
    {
        /** @var TUser $inputData */
        $inputData = $inputObject->toArray();

        if (null !== $inputData['password']) {
            $plain  = $inputData['password'];
            $hashed = $this->security->hash($plain);

            $inputData['password'] = $hashed;
        }

        return $this->mapper->input($inputData);
    }
}
