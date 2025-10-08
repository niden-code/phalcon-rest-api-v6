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

namespace Phalcon\Api\Domain\ADR\Domain;

/**
 * @phpstan-type THelloInput array{}
 * @phpstan-type TUserInput array{
 *     userId?: int
 * }
 * @phpstan-type TRequestQuery array<array-key, bool|int|string>
 */
final class InputTypes
{
}
