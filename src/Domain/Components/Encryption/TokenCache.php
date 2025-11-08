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

namespace Phalcon\Api\Domain\Components\Encryption;

use DateTimeImmutable;
use Phalcon\Api\Domain\ADR\InputTypes;
use Phalcon\Api\Domain\Components\Constants\Cache as CacheConstants;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\DataSource\User\User;
use Phalcon\Api\Domain\Components\DataSource\User\UserRepositoryInterface;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Cache;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

use function str_replace;

/**
 * Small component to issue/rotate/revoke tokens and
 * interact with cache.
 *
 * @phpstan-import-type TTokenIssue from TokenManagerInterface
 * @phpstan-import-type TValidatorErrors from InputTypes
 */
final class TokenCache implements TokenCacheInterface
{
    public function __construct(
        private readonly Cache $cache,
    ) {
    }

    /**
     * @param EnvManager $env
     * @param User       $domainUser
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function invalidateForUser(
        EnvManager $env,
        User $domainUser
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
        $redis   = $this->cache->getAdapter();
        $pattern = CacheConstants::getCacheTokenKey($domainUser, '');
        $keys    = $redis->getKeys($pattern);
        /** @var string $prefix */
        $prefix  = $env->get('CACHE_PREFIX', '-rest-', 'string');
        $newKeys = [];
        /** @var string $key */
        foreach ($keys as $key) {
            $newKeys[] = str_replace($prefix, '', $key);
        }

        return $this->cache->deleteMultiple($newKeys);
    }

    /**
     * @param EnvManager $env
     * @param User       $domainUser
     * @param string     $token
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function storeTokenInCache(
        EnvManager $env,
        User $domainUser,
        string $token
    ): bool {
        $cacheKey = CacheConstants::getCacheTokenKey($domainUser, $token);
        /** @var int $expiration */
        $expiration     = $env->get(
            'TOKEN_EXPIRATION',
            CacheConstants::CACHE_TOKEN_EXPIRY,
            'int'
        );
        $expirationDate = (new DateTimeImmutable())
            ->modify('+' . $expiration . ' seconds')
            ->format(Dates::DATE_TIME_FORMAT)
        ;

        $payload = [
            'token'  => $token,
            'expiry' => $expirationDate,
        ];

        return $this->cache->set($cacheKey, $payload, $expiration);
    }
}
