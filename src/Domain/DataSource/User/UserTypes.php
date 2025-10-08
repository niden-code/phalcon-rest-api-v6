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

/**
 * @phpstan-type TUserRecord array{}|array{
 *     usr_id: int,
 *     usr_status_flag: int,
 *     usr_username: string,
 *     usr_password: string
 * }
 *
 * @phpstan-type TUserTransport array{
 *     id: int,
 *     status: int,
 *     username: string,
 *     password: string
 * }
 */
final class UserTypes
{
}
