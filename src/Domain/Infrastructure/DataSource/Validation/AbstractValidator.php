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

use Phalcon\Api\Domain\Infrastructure\CommandBus\CommandInterface;
use Phalcon\Api\Domain\Infrastructure\Enums\Validator\ValidatorEnumInterface;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Filter\Validation\ValidatorInterface as PhalconValidator;

abstract class AbstractValidator implements ValidatorInterface
{
    protected string $fields = '';

    public function __construct(
        private readonly ValidationInterface $validation
    ) {
    }

    /**
     * @param CommandInterface $command
     *
     * @return list<array<int, string>>
     */
    protected function runValidations(CommandInterface $command): array
    {
        $enum = $this->fields;
        /** @var ValidatorEnumInterface[] $elements */
        $elements = $enum::cases();

        /** @var ValidatorEnumInterface $element */
        foreach ($elements as $element) {
            $validators = $element->validators();
            foreach ($validators as $validator) {
                /** @var PhalconValidator $validatorObject */
                $validatorObject = new $validator(
                    [
                        'allowEmpty' => $element->allowEmpty(),
                    ]
                );
                $this->validation->add($element->name, $validatorObject);
            }
        }

        $this->validation->validate($command);
        $messages = $this->validation->getMessages();

        $results = [];
        foreach ($messages as $message) {
            $results[] = [$message->getMessage()];
        }


        return $results;
    }
}
