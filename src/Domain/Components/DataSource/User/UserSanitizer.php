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

use Phalcon\Api\Domain\Components\DataSource\SanitizerInterface;
use Phalcon\Filter\Filter;

/**
 * @phpstan-import-type TUser from UserTypes
 */
final class UserSanitizer implements SanitizerInterface
{
    public function __construct(
        private readonly Filter $filter,
    ) {
    }

    /**
     * Return a sanitized array of the input
     *
     * @param TUser $input
     *
     * @return TUser
     */
    public function sanitize(array $input): array
    {
        $fields = [
            'id'            => 0,
            'status'        => 0,
            'email'         => null,
            'password'      => null,
            'namePrefix'    => null,
            'nameFirst'     => null,
            'nameLast'      => null,
            'nameMiddle'    => null,
            'nameSuffix'    => null,
            'issuer'        => null,
            'tokenPassword' => null,
            'tokenId'       => null,
            'preferences'   => null,
            'createdDate'   => null,
            'createdUserId' => 0,
            'updatedDate'   => null,
            'updatedUserId' => 0,
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

        /** @var TUser $sanitized */
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
            'id',
            'status',
            'createdUserId',
            'updatedUserId' => Filter::FILTER_ABSINT,
            'email'         => Filter::FILTER_EMAIL,
            'password',
            'tokenId',
            'tokenPassword' => '', // Password will be distorted
            default         => Filter::FILTER_STRING,
        };
    }
}
