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

namespace Phalcon\Api\Domain\Components\DataSource\Auth;

use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;
use Phalcon\Filter\Filter;

final class AuthSanitizer implements SanitizerInterface
{
    public function __construct(
        private readonly Filter $filter,
    ) {
    }

    /**
     * Return a sanitized array of the input
     *
     * @param array $input
     *
     * @return array
     */
    public function sanitize(array $input): array
    {
        $fields = [
            'email'    => null,
            'password' => null,
            'token'    => null,
        ];

        /**
         * Sanitize all the fields. The fields can be `null` meaning they
         * were not defined with the input or a value. If the value exists
         * we will sanitize it
         */
        $sanitized = [];
        foreach ($fields as $name => $defaultValue) {
            $value = $input[$name] ?? $defaultValue;

            if (null !== $value) {
                $sanitizer = $this->getSanitizer($name);
                if (true !== empty($sanitizer)) {
                    $value = $this->filter->sanitize($value, $sanitizer);
                }
            }
            $sanitized[$name] = $value;
        }

        return $sanitized;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getSanitizer(string $name): string
    {
        return match ($name) {
            'email' => Filter::FILTER_EMAIL,
            default => '',
        };
    }
}
