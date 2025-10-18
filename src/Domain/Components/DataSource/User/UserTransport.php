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

use Phalcon\Api\Domain\Components\Exceptions\InvalidConfigurationArgumentException;

/**
 * The domain representation of a user.
 *
 * @method int         getId()
 * @method int         getTenantId()
 * @method string      getStatus()
 * @method string      getEmail()
 * @method string      getPassword()
 * @method string      getNamePrefix()
 * @method string      getNameFirst()
 * @method string      getNameMiddle()
 * @method string      getNameLast()
 * @method string      getNameSuffix()
 * @method string      getIssuer()
 * @method string      getTokenPassword()
 * @method string      getTokenId()
 * @method string      getPreferences()
 * @method string      getCreatedDate()
 * @method int|null    getCreatedUserId()
 * @method string      getUpdatedDate()
 * @method int|null    getUpdatedUserId()
 *
 * @phpstan-import-type TUserRecord from UserTypes
 * @phpstan-import-type TUserTransport from UserTypes
 */
final class UserTransport
{
    /** @var TUserTransport */
    private array $store;

    /**
     * @param TUserRecord $input
     */
    public function __construct(array $input)
    {
        $this->store = [
            'id'            => (int)($input['usr_id'] ?? 0),
            'status'        => (int)($input['usr_status_flag'] ?? 0),
            'email'         => (string)($input['usr_email'] ?? ''),
            'password'      => (string)($input['usr_password'] ?? ''),
            'namePrefix'    => (string)($input['usr_name_prefix'] ?? ''),
            'nameFirst'     => (string)($input['usr_name_first'] ?? ''),
            'nameMiddle'    => (string)($input['usr_name_middle'] ?? ''),
            'nameLast'      => (string)($input['usr_name_last'] ?? ''),
            'nameSuffix'    => (string)($input['usr_name_suffix'] ?? ''),
            'issuer'        => (string)($input['usr_issuer'] ?? ''),
            'tokenPassword' => (string)($input['usr_token_password'] ?? ''),
            'tokenId'       => (string)($input['usr_token_id'] ?? ''),
            'preferences'   => (string)($input['usr_preferences'] ?? ''),
            'createdDate'   => (string)($input['usr_created_date'] ?? ''),
            'createdUserId' => (int)($input['usr_created_usr_id'] ?? 0),
            'updatedDate'   => (string)($input['usr_updated_date'] ?? ''),
            'updatedUserId' => (int)($input['usr_updated_usr_id'] ?? 0),
        ];

        $this->store['fullName'] = $this->getFullName();
    }

    /**
     * @param string       $name
     * @param array<mixed> $arguments
     *
     * @return int|string
     */
    public function __call(string $name, array $arguments): int | string
    {
        return match ($name) {
            'getId'            => $this->store['id'],
            'getStatus'        => $this->store['status'],
            'getEmail'         => $this->store['email'],
            'getPassword'      => $this->store['password'],
            'getNamePrefix'    => $this->store['namePrefix'],
            'getNameFirst'     => $this->store['nameFirst'],
            'getNameMiddle'    => $this->store['nameMiddle'],
            'getNameLast'      => $this->store['nameLast'],
            'getNameSuffix'    => $this->store['nameSuffix'],
            'getIssuer'        => $this->store['issuer'],
            'getTokenPassword' => $this->store['tokenPassword'],
            'getTokenId'       => $this->store['tokenId'],
            'getPreferences'   => $this->store['preferences'],
            'getCreatedDate'   => $this->store['createdDate'],
            'getCreatedUserId' => $this->store['createdUserId'],
            'getUpdatedDate'   => $this->store['updatedDate'],
            'getUpdatedUserId' => $this->store['updatedUserId'],
            default            => throw new InvalidConfigurationArgumentException(
                'The ' . $name . ' method is not supported. ['
                . json_encode($arguments) . ']',
            ),
        };
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim(
            $this->getNameLast()
            . ', '
            . $this->getNameFirst()
            . ' '
            . $this->getNameMiddle()
        );
    }

    public function isEmpty(): bool
    {
        return 0 === $this->store['id'];
    }

    /**
     * @return array<int, TUserTransport>
     */
    public function toArray(): array
    {
        return [$this->store['id'] => $this->store];
    }
}
