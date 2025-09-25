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

namespace Phalcon\Api\Domain\Services\Http;

use Exception;
use Phalcon\Api\Domain\Constants\Dates;
use Phalcon\Http\Response as PhalconResponse;
use Phalcon\Http\Response\Exception as ResponseException;

use function json_encode;
use function sha1;

class Response extends PhalconResponse
{
    private array $payload = [];

    /**
     * @return $this
     * @throws Exception
     */
    public function render(): self
    {
        $this
            ->calculateMeta()
            ->setJsonContent($this->payload)
        ;

        return $this;
    }

    /**
     * @param int    $code
     * @param string $message
     *
     * @return $this
     * @throws ResponseException
     */
    public function withCode(int $code, string $message = ''): self
    {
        $this->setStatusCode($code, $message);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     * @throws Exception
     */
    public function withPayloadData(array $data): self
    {
        if (empty($this->payload)) {
            $this->initPayload();
        }

        $this->payload['data'] = $data;

        return $this;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function withPayloadErrors(array $errors): self
    {
        if (empty($this->payload)) {
            $this->initPayload();
        }

        $this->payload['errors']          = $errors;
        $this->payload['meta']['code']    = 3000;
        $this->payload['meta']['message'] = 'error';

        return $this;
    }

    /**
     * @return self
     * @throws Exception
     */
    private function calculateMeta(): self
    {
        $payload   = [
            'data'   => $this->payload['data'],
            'errors' => $this->payload['errors'],
        ];
        $encoded   = json_encode($payload);
        $encoded   = (false === $encoded) ? '' : $encoded;
        $timestamp = Dates::toUTC();
        $hash      = sha1($timestamp . $encoded);
        $eTag      = sha1($encoded);

        $this->payload['meta']['timestamp'] = $timestamp;
        $this->payload['meta']['hash']      = $hash;

        $this
            ->setHeader('ETag', $eTag)
        ;

        return $this;
    }

    /**
     * @return void
     */
    private function initPayload(): void
    {
        $this->payload = [
            'data'   => [],
            'errors' => [],
            'meta'   => [
                'code'      => HttpCodesEnum::OK->value,
                'hash'      => '',
                'message'   => 'success',
                'timestamp' => '',
            ],
        ];
    }
}
