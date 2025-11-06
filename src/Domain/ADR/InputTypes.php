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

namespace Phalcon\Api\Domain\ADR;

/**
 * @phpstan-type TLoginInput array{
 *     email?: string,
 *     password?: string
 * }
 *
 * @phpstan-type TLogoutInput array{
 *     token?: string
 * }
 *
 * @phpstan-type TRefreshInput array{
 *     token?: string
 * }
 *
 * @phpstan-type TUserInput array{
 *     id?: int,
 *     status?: int,
 *     email?: string,
 *     password?: string,
 *     namePrefix?: string,
 *     nameFirst?: string,
 *     nameMiddle?: string,
 *     nameLast?: string,
 *     nameSuffix?: string,
 *     issuer?: string,
 *     tokenPassword?: string,
 *     tokenId?: string,
 *     preferences?: string,
 *     createdDate?: string,
 *     createdUserId?: int,
 *     updatedDate?: string,
 *     updatedUserId?: int,
 * }
 *
 * @phpstan-type TUserSanitizedInsertInput array{
 *     status: int,
 *     email: string,
 *     password: string,
 *     namePrefix: string,
 *     nameFirst: string,
 *     nameMiddle: string,
 *     nameLast: string,
 *     nameSuffix: string,
 *     issuer: string,
 *     tokenPassword: string,
 *     tokenId: string,
 *     preferences: string,
 *     createdDate: string,
 *     createdUserId: int,
 *     updatedDate: string,
 *     updatedUserId: int,
 * }
 *
 * @phpstan-type TUserSanitizedUpdateInput array{
 *     id: int,
 *     status: int,
 *     email: string,
 *     password: string,
 *     namePrefix: string,
 *     nameFirst: string,
 *     nameMiddle: string,
 *     nameLast: string,
 *     nameSuffix: string,
 *     issuer: string,
 *     tokenPassword: string,
 *     tokenId: string,
 *     preferences: string,
 *     updatedDate: string,
 *     updatedUserId: int,
 * }
 *
 * @phpstan-type TRequestQuery array<array-key, bool|int|string>
 *
 * @phpstan-type TValidationErrors array<int, array<int, string>>
 */
final class InputTypes
{
}
