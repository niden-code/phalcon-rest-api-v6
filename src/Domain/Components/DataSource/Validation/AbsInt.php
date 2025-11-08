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

namespace Phalcon\Api\Domain\Components\DataSource\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;

use Phalcon\Filter\Validation\Validator\Numericality;

use function preg_match;

class AbsInt extends Numericality
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field is not a valid absolute integer and greater than 0";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $result = parent::validate($validation, $field);

        if (false === $result) {
            return false;
        }

        // Dump spaces in the string if we have any
        $value = $validation->getValue($field);
        $value = abs((int)$value);

        if ($value <= 0) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
