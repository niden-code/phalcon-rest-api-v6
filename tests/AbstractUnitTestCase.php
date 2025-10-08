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

use PDO;
use Phalcon\Api\Tests\Fixtures\Domain\Migrations\UsersMigration;
use Phalcon\DataMapper\Pdo\Connection;
use PHPUnit\Framework\TestCase;

use function uniqid;

abstract class AbstractUnitTestCase extends TestCase
{
    protected ?Connection $connection = null;

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

    /**
     * @param UsersMigration $migration
     * @param array          $fields
     *
     * @return array
     */
    public function getNewUser(UsersMigration $migration, array $fields = []): array
    {
        $status   = $fields['usr_status_flag'] ?? 1;
        $username = $fields['usr_username'] ?? uniqid('name-');
        $password = $fields['usr_password'] ?? $this->getStrongPassword();

        $migration->insert(null, $status, $username, $password);

        $dbUser = $this->getFromDatabase(
            'co_users',
            [
                'usr_username' => $username,
            ]
        );

        return $dbUser[0];
    }

    /**
     * Return a long series of strings to be used as a password
     *
     * @return string
     */
    public function getStrongPassword(): string
    {
        return substr(base64_encode(random_bytes(512)), 0, 128);
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
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
        $sql   = 'SELECT * FROM ' . $table . ' WHERE ';
        $where = [];
        foreach ($criteria as $key => $value) {
            $val = $value;
            if (true === is_string($value)) {
                $val = '"' . $value . '"';
            }

            $where[] = $key . ' = ' . $val;
        }

        $sql .= implode(' AND ', $where);

        $result  = $this->connection?->query($sql);
        $records = $result?->fetchAll(PDO::FETCH_ASSOC);

        return $records;
    }
}
