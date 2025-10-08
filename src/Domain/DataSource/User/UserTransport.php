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

namespace Phalcon\Api\Domain\DataSource\User;

use Phalcon\Api\Domain\Exceptions\InvalidConfigurationArgumentException;

/**
 * @method int    getId()
 * @method int    getStatus()
 * @method string getUsername()
 * @method string getPassword()
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
            'id'       => (int)($input['usr_id'] ?? 0),
            'status'   => (int)($input['usr_status_flag'] ?? 0),
            'username' => (string)($input['usr_username'] ?? ''),
            'password' => (string)($input['usr_password'] ?? ''),
        ];
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
            'getId'       => $this->store['id'],
            'getStatus'   => $this->store['status'],
            'getUsername' => $this->store['username'],
            'getPassword' => $this->store['password'],
            default       => throw new InvalidConfigurationArgumentException(
                'The ' . $name . ' method is not supported. ['
                . json_encode($arguments) . ']',
            ),
        };
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
