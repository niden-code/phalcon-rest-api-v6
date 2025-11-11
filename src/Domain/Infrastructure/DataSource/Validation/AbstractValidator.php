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

namespace Phalcon\Api\Domain\Infrastructure\DataSource\Validation;

use Phalcon\Api\Domain\Infrastructure\DataSource\Auth\DTO\AuthInput;
use Phalcon\Api\Domain\Infrastructure\Enums\Input\ValidatorEnumInterface;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Filter\Validation\ValidatorInterface as PhalconValidator;

abstract class AbstractValidator implements ValidatorInterface
{
    protected string $fields = '';

    public function __construct(
        private ValidationInterface $validation
    ) {
    }

    /**
     * @param AuthInput $input
     *
     * @return list<array<int, string>>
     */
    protected function runValidations(mixed $input): array
    {
        $enum     = $this->fields;
        /** @var ValidatorEnumInterface[] $elements */
        $elements = $enum::cases();

        /** @var ValidatorEnumInterface $element */
        foreach ($elements as $element) {
            $validators = $element->validators();
            foreach ($validators as $validator) {
                /** @var PhalconValidator $validatorObject */
                $validatorObject = new $validator();
                $this->validation->add($element->name, $validatorObject);
            }
        }

        $this->validation->validate($input);
        $messages = $this->validation->getMessages();

        $results = [];
        foreach ($messages as $message) {
            $results[] = [$message->getMessage()];
        }


        return $results;
    }
}
