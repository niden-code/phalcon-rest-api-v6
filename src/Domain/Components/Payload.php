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

namespace Phalcon\Api\Domain\Components;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Responder\ResponderTypes;
use Phalcon\Domain\Payload as PhalconPayload;

/**
 * @phpstan-import-type TData from ResponderTypes
 * @phpstan-import-type TErrors from ResponderTypes
 */
final class Payload extends PhalconPayload
{
    /**
     * @param string  $status
     * @param TData   $data
     * @param TErrors $errors
     */
    private function __construct(
        string $status,
        array $data = [],
        array $errors = []
    ) {
        $result = [];

        if (true !== empty($data)) {
            $result = [
                'data' => $data,
            ];
        }

        if (true !== empty($errors)) {
            $result = [
                'errors' => $errors,
            ];
        }

        parent::__construct($status, $result);
    }

    /**
     * @param TData $data
     *
     * @return self
     */
    public static function created(array $data): self
    {
        return new self(DomainStatus::CREATED, $data);
    }

    /**
     * @param TData $data
     *
     * @return self
     */
    public static function deleted(array $data): self
    {
        return new self(DomainStatus::DELETED, $data);
    }

    /**
     * @param TErrors $errors
     *
     * @return self
     */
    public static function error(array $errors): self
    {
        return new self(status: DomainStatus::ERROR, errors: $errors);
    }

    /**
     * @param TErrors $errors
     *
     * @return self
     */
    public static function invalid(array $errors): self
    {
        return new self(status: DomainStatus::INVALID, errors: $errors);
    }

    /**
     * @return self
     */
    public static function notFound(): self
    {
        return new self(
            status: DomainStatus::NOT_FOUND,
            errors: [['Record(s) not found']]
        );
    }

    /**
     * @param TData $data
     *
     * @return self
     */
    public static function success(array $data): self
    {
        return new self(DomainStatus::SUCCESS, $data);
    }

    /**
     * @param TErrors $errors
     *
     * @return self
     */
    public static function unauthorized(array $errors): self
    {
        return new self(status: DomainStatus::UNAUTHORIZED, errors: $errors);
    }

    /**
     * @param TData $data
     *
     * @return self
     */
    public static function updated(array $data): self
    {
        return new self(DomainStatus::UPDATED, $data);
    }
}
