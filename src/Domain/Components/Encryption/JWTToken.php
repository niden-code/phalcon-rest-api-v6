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
use InvalidArgumentException;
use Phalcon\Api\Domain\Components\Cache\Cache;
use Phalcon\Api\Domain\Components\DataSource\QueryRepository;
use Phalcon\Api\Domain\Components\DataSource\User\UserTransport;
use Phalcon\Api\Domain\Components\DataSource\User\UserTypes;
use Phalcon\Api\Domain\Components\Enums\Common\FlagsEnum;
use Phalcon\Api\Domain\Components\Enums\Common\JWTEnum;
use Phalcon\Api\Domain\Components\Env\EnvManager;
use Phalcon\Api\Domain\Components\Exceptions\TokenValidationException;
use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Encryption\Security\JWT\Token\Parser;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Encryption\Security\JWT\Validator;
use Phalcon\Support\Helper\Json\Decode;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 * @phpstan-import-type TUserDbRecord from UserTypes
 * @phpstan-type TValidatorErrors array{}|array<int, string>
 */
final class JWTToken
{
    /**
     * @var Parser|null
     */
    private ?Parser $parser = null;

    public function __construct(
        private readonly EnvManager $env
    ) {
    }

    /**
     * Returns the string token
     *
     * @param TUserDbRecord $user
     *
     * @return string
     */
    public function getForUser(array $user): string
    {
        /** @var int $expiration */
        $expiration = $this->env->get(
            'TOKEN_EXPIRATION',
            Cache::CACHE_TOKEN_EXPIRY,
            'int'
        );

        $now       = new DateTimeImmutable();
        $expiresAt = $now->modify('+' . $expiration . ' seconds');

        return $this->generateTokenForUser($user, $now, $expiresAt);
    }

    /**
     * Return the JWT Token object
     *
     * @param string $token
     *
     * @return Token
     */
    public function getObject(string $token): Token
    {
        try {
            if (null === $this->parser) {
                $this->parser = new Parser(new Decode());
            }

            $tokenObject = $this->parser->parse($token);
        } catch (InvalidArgumentException $ex) {
            throw TokenValidationException::new($ex->getMessage());
        }

        return $tokenObject;
    }

    /**
     * @param QueryRepository $repository
     * @param Token           $token
     *
     * @return TUserRecord
     */
    public function getUser(
        QueryRepository $repository,
        Token $token,
    ): array {
        /** @var string $issuer */
        $issuer = $token->getClaims()->get(JWTEnum::Issuer->value);
        /** @var string $tokenId */
        $tokenId = $token->getClaims()->get(JWTEnum::Id->value);
        /** @var string $userId */
        $userId = $token->getClaims()->get(JWTEnum::UserId->value);

        $criteria = [
            'usr_id'          => $userId,
            'usr_status_flag' => FlagsEnum::Active->value,
            'usr_issuer'      => $issuer,
            'usr_token_id'    => $tokenId,
        ];

        /** @var TUserRecord $user */
        $user = $repository->user()->findOneBy($criteria);

        return $user;
    }

    /**
     * @param Token         $tokenObject
     * @param UserTransport $user
     *
     * @return TValidatorErrors
     */
    public function validate(
        Token $tokenObject,
        UserTransport $user
    ): array {
        $validator = new Validator($tokenObject);
        $signer    = new Hmac();
        $now       = new DateTimeImmutable();

        $validator
            ->validateId($user->getTokenId())
            ->validateAudience($this->getTokenAudience())
            ->validateIssuer($user->getIssuer())
            ->validateNotBefore($now->getTimestamp())
            ->validateIssuedAt($now->getTimestamp())
            ->validateExpiration($now->getTimestamp())
            ->validateSignature($signer, $user->getTokenPassword())
            ->validateClaim(JWTEnum::UserId->value, $user->getId())
        ;

        /** @var TValidatorErrors $errors */
        $errors = $validator->getErrors();

        return $errors;
    }

    /**
     * @param TUserDbRecord     $user
     * @param DateTimeImmutable $now
     * @param DateTimeImmutable $expiresAt
     * @param bool              $isRefresh
     *
     * @return string
     */
    private function generateTokenForUser(
        array $user,
        DateTimeImmutable $now,
        DateTimeImmutable $expiresAt,
        bool $isRefresh = true
    ): string {
        $tokenBuilder = new Builder(new Hmac());
        /** @var string $issuer */
        $issuer = $user['usr_issuer'];
        /** @var string $tokenPassword */
        $tokenPassword = $user['usr_token_password'];
        /** @var string $tokenId */
        $tokenId = $user['usr_token_id'];
        /** @var string $userId */
        $userId = $user['usr_id'];

        $tokenObject = $tokenBuilder
            ->setIssuer($issuer)
            ->setAudience($this->getTokenAudience())
            ->setId($tokenId)
            ->setNotBefore($now->getTimestamp())
            ->setIssuedAt($now->getTimestamp())
            ->setExpirationTime($expiresAt->getTimestamp())
            ->setPassphrase($tokenPassword)
            ->addClaim(JWTEnum::UserId->value, $userId)
            ->addClaim(JWTEnum::Refresh->value, $isRefresh)
            ->getToken()
        ;

        return $tokenObject->getToken();
    }


    /**
     * @return string
     */
    private function getTokenAudience(): string
    {
        /** @var string $audience */
        $audience = $this->env->get(
            'TOKEN_AUDIENCE',
            'https://rest-api.phalcon.io'
        );

        return $audience;
    }
}
