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

namespace Phalcon\Api\Tests;

use DateTimeImmutable;
use Faker\Factory;
use PDO;
use Phalcon\Api\Domain\Components\Constants\Dates;
use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\Encryption\Security;
use Phalcon\Api\Domain\Components\Enums\Common\JWTEnum;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * @var Connection|null
     */
    protected ?Connection $connection = null;

    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @param string $fileName
     * @param string $stream
     *
     * @return void
     */
    public function assertFileContentsContains(string $fileName, string $stream): void
    {
        $contents = file_get_contents($fileName);
        $this->assertStringContainsString($stream, $contents);
    }

    public function assertInDatabase(string $table, array $criteria = []): void
    {
        $records = $this->getFromDatabase($table, $criteria);

        $this->assertNotEmpty($records);
    }

    public function assertNotInDatabase(string $table, array $criteria = []): void
    {
        $records = $this->getFromDatabase($table, $criteria);

        $this->assertEmpty($records);
    }

    public function getConnection(): Connection
    {
        if (null === $this->connection) {
            /** @var Connection $connection */
            $connection = $this->container->getShared(Container::CONNECTION);

            $this->connection = $connection;
        }

        return $this->connection;
    }

    /**
     * @param UsersMigration $migration
     * @param array          $fields
     *
     * @return array
     */
    public function getNewUser(UsersMigration $migration, array $fields = []): array
    {
        $userData = $this->getNewUserData($fields);
        $userId   = $migration->insert(
            null,
            $userData['usr_status_flag'],
            $userData['usr_email'],
            $userData['usr_password'],
            $userData['usr_name_prefix'],
            $userData['usr_name_first'],
            $userData['usr_name_middle'],
            $userData['usr_name_last'],
            $userData['usr_name_suffix'],
            $userData['usr_issuer'],
            $userData['usr_token_password'],
            $userData['usr_token_id'],
            $userData['usr_preferences'],
            $userData['usr_created_date'],
            $userData['usr_created_usr_id'],
            $userData['usr_updated_date'],
            $userData['usr_updated_usr_id']
        );

        $dbUser = $this->getFromDatabase(
            'co_users',
            [
                'usr_id' => $userId,
            ]
        );

        return $dbUser[0];
    }

    public function getNewUserData(array $fields = []): array
    {
        $faker    = Factory::create();
        $password = $fields['usr_password'] ?? $this->getStrongPassword();
        /** @var Security $security */
        $security = $this->container->get(Container::SECURITY);

        $password = $security->hash($password);

        return [
            'usr_id'             => 0,
            'usr_status_flag'    => $fields['usr_status_flag'] ?? 1,
            'usr_email'          => $fields['usr_email'] ?? $faker->email(),
            'usr_password'       => $password,
            'usr_name_prefix'    => $fields['usr_name_prefix'] ?? $faker->title(),
            'usr_name_first'     => $fields['usr_name_first'] ?? $faker->firstName(),
            'usr_name_middle'    => $fields['usr_name_middle'] ?? $faker->firstName(),
            'usr_name_last'      => $fields['usr_name_last'] ?? $faker->lastName(),
            'usr_name_suffix'    => $fields['usr_name_suffix'] ?? $faker->suffix(),
            'usr_issuer'         => $fields['usr_issuer'] ?? $faker->url(),
            'usr_token_password' => $fields['usr_token_password'] ?? $this->getStrongPassword(),
            'usr_token_id'       => $fields['usr_token_id'] ?? $this->getStrongPassword(),
            'usr_preferences'    => $fields['usr_preferences'] ?? '',
            'usr_created_date'   => $fields['usr_created_date'] ?? $faker->date(Dates::DATE_TIME_FORMAT),
            'usr_created_usr_id' => $fields['usr_created_usr_id'] ?? 0,
            'usr_updated_date'   => $fields['usr_updated_date'] ?? $faker->date(Dates::DATE_TIME_FORMAT),
            'usr_updated_usr_id' => $fields['usr_updated_usr_id'] ?? 0,
        ];
    }

    /**
     * Return a long series of strings to be used as a password
     *
     * @return string
     */
    public function getStrongPassword(): string
    {
        $password = base64_encode(random_bytes(128));
        $special  = '!@#$%^&*()-_=+[]{}|;:,.<>?/~`';

        $passwordArray = str_split($password);
        $specialArray  = str_split($special);

        $result = array_merge($passwordArray, $specialArray);
        shuffle($result);

        $password = implode('', $result);

        return substr($password, 0, 128);
    }

    /**
     * @return string
     */
    public function getUserToken(array $user): string
    {
        $audience = $user['audience'] ?? 'https://rest-api.phalcon.io';
        $expires  = $user['expires'] ?? '+60 seconds';
        $algo     = $user['algo'] ?? 'sha512';

        $tokenBuilder = new Builder(new Hmac($algo));
        /** @var non-empty-string $issuer */
        $issuer = $user['usr_issuer'];
        /** @var non-empty-string $password */
        $password = $user['usr_token_password'];
        /** @var non-empty-string $tokenId */
        $tokenId = $user['usr_token_id'];
        /** @var non-empty-string $userId */
        $userId = $user['usr_id'];
        $now    = new DateTimeImmutable();
        /**
         *  Just in case so that the token nbf and iss are correct
         */
        $now       = $now->modify('-2 seconds');
        $expiresAt = $now->modify($expires);

        $tokenObject = $tokenBuilder
            ->setIssuer($issuer)
            ->setAudience($audience)
            ->setId($tokenId)
            ->setNotBefore($now->getTimestamp())
            ->setIssuedAt($now->getTimestamp())
            ->setExpirationTime($expiresAt->getTimestamp())
            ->setPassphrase($password)
            ->addClaim(JWTEnum::UserId->value, $userId)
            ->addClaim(JWTEnum::Refresh->value, false)
            ->getToken()
        ;

        return $tokenObject->getToken();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * @param string $table
     * @param array  $criteria
     *
     * @return array
     */
    protected function getFromDatabase(
        string $table,
        array $criteria
    ): array {
        $where  = [];
        $params = [];
        foreach ($criteria as $key => $value) {
            $param          = ':' . $key;
            $where[]        = $key . ' = ' . $param;
            $params[$param] = $value;
        }
        $sql = 'SELECT * FROM ' . $table;
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $stmt = $this->connection?->prepare($sql);
        $stmt?->execute($params);
        $records = $stmt?->fetchAll(PDO::FETCH_ASSOC);

        return $records;
    }
}
