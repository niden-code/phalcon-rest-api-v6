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

namespace Phalcon\Api\Domain\Components\Cache;

use DateTimeImmutable;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Cache as PhalconCache;
use Psr\SimpleCache\InvalidArgumentException;

use function sha1;

class Cache extends PhalconCache
{
    /** @var int */
    public const CACHE_LIFETIME_DAY = 86400;
    /** @var int */
    public const CACHE_LIFETIME_HOUR = 3600;
    /**
     * Cache Timeouts
     */
    /** @var int */
    public const CACHE_LIFETIME_MINUTE = 60;
    /** @var int */
    public const CACHE_LIFETIME_MONTH = 2592000;
    /**
     * Default token expiry - 4 hours
     */
    /** @var int */
    public const CACHE_TOKEN_EXPIRY = 14400;
    /**
     * Cache masks
     */
    /** @var string */
    private const MASK_TOKEN_USER = 'tk-%s-%s';

    /**
     * @param UserTransport $domainUser
     * @param string        $token
     *
     * @return string
     */
    public function getCacheTokenKey(UserTransport $domainUser, string $token): string
    {
        $tokenString = '';
        if (true !== empty($token)) {
            $tokenString = sha1($token);
        }

        return sprintf(
            self::MASK_TOKEN_USER,
            $domainUser->getId(),
            $tokenString
        );
    }

    /**
     * @param EnvManager    $env
     * @param UserTransport $domainUser
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function invalidateForUser(
        EnvManager $env,
        UserTransport $domainUser
    ): bool {
        /**
         * We could store the tokens in the database but this way is faster
         * and Redis also has a TTL which auto expires elements.
         *
         * To get all the keys for a user, we use the underlying adapter
         * of the cache which is Redis and call the `getKeys()` on it. The
         * keys will come back with the prefix defined in the adapter. In order
         * to delete them, we need to remove the prefix because `delete()` will
         * automatically prepend each key with it.
         */
        /** @var Redis $redis */
        $redis   = $this->getAdapter();
        $pattern = $this->getCacheTokenKey($domainUser, '');
        $keys    = $redis->getKeys($pattern);
        /** @var string $prefix */
        $prefix  = $env->get('CACHE_PREFIX', '-rest-', 'string');
        $newKeys = [];
        /** @var string $key */
        foreach ($keys as $key) {
            $newKeys[] = str_replace($prefix, '', $key);
        }

        return $this->deleteMultiple($newKeys);
    }

    /**
     * @param EnvManager    $env
     * @param UserTransport $domainUser
     * @param string        $token
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function storeTokenInCache(
        EnvManager $env,
        UserTransport $domainUser,
        string $token
    ): bool {
        $cacheKey = $this->getCacheTokenKey($domainUser, $token);
        /** @var int $expiration */
        $expiration     = $env->get('TOKEN_EXPIRATION', self::CACHE_TOKEN_EXPIRY, 'int');
        $expirationDate = (new DateTimeImmutable())
            ->modify('+' . $expiration . ' seconds')
            ->format(Dates::DATE_TIME_FORMAT)
        ;

        $payload = [
            'token'  => $token,
            'expiry' => $expirationDate,
        ];

        return $this->set($cacheKey, $payload, $expiration);
    }
}
