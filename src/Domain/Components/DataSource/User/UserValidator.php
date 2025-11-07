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

final class UserValidator
{
    /**
     * Validate a UserInput and return an array of errors.
     * Empty array means valid.
     *
     * @param UserInput $input
     *
     * @return array
     */
    public function validate(UserInput $input): array
    {
        $errors   = [];
        $required = [
            'email',
            'password',
            'issuer',
            'tokenPassword',
            'tokenId',
        ];

        foreach ($required as $name) {
            $value = $input->$name;
            if (true === empty($value)) {
                $errors[] = ['Field ' . $name . ' cannot be empty.'];
            }
        }

        /**
         * @todo add validators
         */

        return $errors;
    }
}
