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

namespace Phalcon\Api\Domain\Services\Http;

/**
 * This class is used to define phpstan types so that they are all in one
 * place.
 *
 * @phpstan-type TData array<array-key, mixed>|array{}
 * @phpstan-type TErrors array<array-key, array<int, string>>|array{}
 * @phpstan-type TResponsePayload array{
 *      data: TData,
 *      errors: TErrors,
 *      meta: array{
 *          code: int,
 *          hash: string,
 *          message: string,
 *          timestamp: string
 *     }
 * }
 */
final class ResponseTypes
{
}
