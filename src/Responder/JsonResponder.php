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

namespace Phalcon\Api\Responder;

use DateTimeImmutable;
use DateTimeZone;
use Phalcon\Api\Domain\Interfaces\ResponderInterface;
use Phalcon\Domain\Payload;
use Phalcon\Http\ResponseInterface;

use function json_encode;

final class JsonResponder implements ResponderInterface
{
    public function __construct(
        private ResponseInterface $response
    ) {
    }

    public function __invoke(Payload $payload): ResponseInterface
    {
        $result  = $payload->getResult();
        /** @var string $content */
        $content = $result['results'];

        $timestamp = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $dateTime  = $timestamp->format('Y-m-d H:i:s');
        $output = [
            'data'   => [
                $content
            ],
            'errors' => [],
            'meta'   => [
                'code'      => 200,
                'hash'      => '',
                'message'   => 'success',
                'timestamp' => $dateTime,
            ]
        ];

        $dataErrors = [
            'data'   => $output['data'],
            'errors' => $output['errors'],
        ];
        $encoded  = json_encode($dataErrors);
        $encoded  = (false === $encoded) ? '' : $encoded;
        $hash     = sha1($dateTime . $encoded);
        $eTag     = sha1($encoded);

        $output['meta']['hash'] = $hash;

        $this
            ->response
            ->setContentType('application/json')
            ->setHeader('E-Tag', $eTag)
            ->setJsonContent($output)
        ;

        return $this->response;
    }
}
