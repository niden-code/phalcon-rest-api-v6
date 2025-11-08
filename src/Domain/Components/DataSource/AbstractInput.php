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

namespace Phalcon\Api\Domain\Components\DataSource;

use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\DataSource\Interfaces\SanitizerInterface;

/**
 * Base factory for input DTOs.
 *
 * Concrete DTOs must implement protected static function
 *
 * `fromArray(array $sanitized): static`
 *
 * and keep themselves immutable/read\-only.
 *
 * @phpstan-import-type TInputSanitize from InputTypes
 */
abstract class AbstractInput
{
    /**
     * Factory that accepts a SanitizerInterface and returns the concrete DTO.
     *
     * @param SanitizerInterface $sanitizer
     * @param TInputSanitize     $input
     *
     * @return static
     */
    public static function new(SanitizerInterface $sanitizer, array $input): static
    {
        $sanitized = $sanitizer->sanitize($input);

        return static::fromArray($sanitized);
    }

    /**
     * Build the concrete DTO from a sanitized array.
     *
     * @param TInputSanitize $sanitized
     *
     * @return static
     */
    abstract protected static function fromArray(array $sanitized): static;
}
