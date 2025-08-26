<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Api\Tests\Fixtures\Domain;

use PDO;
use Phalcon\Api\Domain\Services\Environment\EnvManager;
use Phalcon\Api\Domain\Services\Exceptions\InvalidConfigurationArguments;
use Phalcon\DataMapper\Pdo\Connection;
use PHPUnit\Framework\Assert;

use function sprintf;

abstract class AbstractDatabaseTestCase extends AbstractUnitTestCase
{
    /**
     * @var Connection|null
     */
    private static ?Connection $connection = null;

    /**
     * @var string
     */
    private static string $password = '';

    /**
     * @var string
     */
    private static string $username = '';

    /**
     * @param string $table
     * @param array  $criteria
     *
     * @return void
     */
    public function assertInDatabase(string $table, array $criteria = []): void
    {
        $records = $this->getFromDatabase($table, $criteria);

        $this->assertNotEmpty($records);
    }

    /**
     * @param string $table
     * @param array  $criteria
     *
     * @return void
     */
    public function assertNotInDatabase(string $table, array $criteria = []): void
    {
        $records = $this->getFromDatabase($table, $criteria);

        $this->assertSame([], $records);
    }

    public function deleteById(string $table, string $field, int $id): int
    {
        $sql = "DELETE FROM $table WHERE $field = $id";

        $connection = self::$connection;
        if (!$result = $connection->exec($sql)) {
            Assert::fail("Failed to delete $table with ID '$id'");
        }

        return $result;
    }

    public function deleteUserById(int $userId): int
    {
        return $this->deleteById('co_users', 'usr_id', $userId);
    }

    /**
     * @return Connection|null
     * @throws InvalidConfigurationArguments
     */
    public static function getConnection(): Connection | null
    {
        if (null === self::$connection) {
            self::$connection = new Connection(
                self::getDatabaseDsn(),
                self::getDatabaseUsername(),
                self::getDatabasePassword()
            );
        }

        return self::$connection;
    }
    /**
     * @return string
     * @throws InvalidConfigurationArguments
     */
    public static function getDatabaseDsn(): string
    {
        $options = self::getDatabaseOptions();

        self::$password = $options['password'];
        self::$username = $options['username'];

        return sprintf(
            "mysql:host=%s;dbname=%s;charset=%s;port=%s",
            $options['host'],
            $options['dbname'],
            $options['port'],
            $options['charset']
        );
    }

    /**
     * @return string
     */
    public static function getDatabaseNow(): string
    {
        return "NOW()";
    }

    /**
     * @return array
     * @throws InvalidConfigurationArguments
     */
    public static function getDatabaseOptions(): array
    {
        return [
            'host'     => EnvManager::getString('DB_HOST', '127.0.0.1'),
            'username' => EnvManager::getString('DB_USER', 'root'),
            'password' => EnvManager::getString('DB_PASSWORD', 'secret'),
            'port'     => EnvManager::getInt('DB_PORT', 5432),
            'dbname'   => EnvManager::getString('DB_NAME', 'phalcon'),
            'schema'   => EnvManager::getString('DB_CHARSET', 'utf8'),
        ];
    }

    /**
     * @return string
     */
    public static function getDatabasePassword(): string
    {
        return self::$password;
    }

    /**
     * @return string
     */
    public static function getDatabaseUsername(): string
    {
        return self::$username;
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        self::$connection = null;
    }

    /**
     * Return records from the database
     *
     * @param string $table
     * @param array  $criteria
     *
     * @return array
     */
    protected function getFromDatabase(string $table, array $criteria = []): array
    {
        $sql   = 'SELECT * FROM ' . $table . ' WHERE ';
        $where = [];
        foreach ($criteria as $key => $value) {
            $val = $value;
            if (is_string($value)) {
                $val = '"' . $value . '"';
            }

            $where[] = $key . ' = ' . $val;
        }
        $sql .= implode(' AND ', $where);

        $connection = self::$connection;
        $result     = $connection->query($sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
