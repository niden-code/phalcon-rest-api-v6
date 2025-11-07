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
use Phalcon\Domain\Payload as PhalconPayload;

final class Payload extends PhalconPayload
{
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

    public static function created(array $data): self
    {
        return new self(DomainStatus::CREATED, $data);
    }

    public static function deleted(array $data): self
    {
        return new self(DomainStatus::DELETED, $data);
    }

    public static function error(array $errors): self
    {
        return new self(status: DomainStatus::ERROR, errors: $errors);
    }

    public static function invalid(array $errors): self
    {
        return new self(status: DomainStatus::INVALID, errors: $errors);
    }

    public static function notFound(array $errors): self
    {
        return new self(status: DomainStatus::NOT_FOUND, errors: $errors);
    }

    public static function success(array $data): self
    {
        return new self(DomainStatus::SUCCESS, $data);
    }

    public static function unauthorized(array $errors): self
    {
        return new self(status: DomainStatus::UNAUTHORIZED, errors: $errors);
    }

    public static function updated(array $data): self
    {
        return new self(DomainStatus::UPDATED, $data);
    }
}
