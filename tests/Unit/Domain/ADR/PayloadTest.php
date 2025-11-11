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

namespace Phalcon\Api\Tests\Unit\Domain\ADR;

use PayloadInterop\DomainStatus;
use Phalcon\Api\Domain\ADR\Payload;
use Phalcon\Api\Tests\AbstractUnitTestCase;

final class PayloadTest extends AbstractUnitTestCase
{
    public function testCreatedContainsDataAndStatusCreated(): void
    {
        $data = ['id' => 1];
        $payload = Payload::created($data);

        $expected = Payload::class;
        $actual = get_class($payload);
        $this->assertSame($expected, $actual);

        $expected = DomainStatus::CREATED;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['data' => $data];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testDeletedContainsDataAndStatusDeleted(): void
    {
        $data = ['deleted' => true];
        $payload = Payload::deleted($data);

        $expected = DomainStatus::DELETED;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['data' => $data];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testUpdatedContainsDataAndStatusUpdated(): void
    {
        $data = ['updated' => true];
        $payload = Payload::updated($data);

        $expected = DomainStatus::UPDATED;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['data' => $data];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testSuccessContainsDataAndStatusSuccess(): void
    {
        $data = ['items' => [1, 2, 3]];
        $payload = Payload::success($data);

        $expected = DomainStatus::SUCCESS;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['data' => $data];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testErrorContainsErrorsAndStatusError(): void
    {
        $errors = [['something' => 'went wrong']];
        $payload = Payload::error($errors);

        $expected = DomainStatus::ERROR;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['errors' => $errors];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testInvalidContainsErrorsAndStatusInvalid(): void
    {
        $errors = [['field' => 'invalid']];
        $payload = Payload::invalid($errors);

        $expected = DomainStatus::INVALID;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['errors' => $errors];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testUnauthorizedContainsErrorsAndStatusUnauthorized(): void
    {
        $errors = [['reason' => 'no access']];
        $payload = Payload::unauthorized($errors);

        $expected = DomainStatus::UNAUTHORIZED;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['errors' => $errors];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }

    public function testNotFoundReturnsDefaultErrorAndStatusNotFound(): void
    {
        $payload = Payload::notFound();

        $expected = DomainStatus::NOT_FOUND;
        $actual = $payload->getStatus();
        $this->assertSame($expected, $actual);

        $expected = ['errors' => [['Record(s) not found']]];
        $actual = $payload->getResult();
        $this->assertSame($expected, $actual);
    }
}
