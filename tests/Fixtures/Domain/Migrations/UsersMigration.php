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

namespace Phalcon\Api\Tests\Fixtures\Domain\Migrations;

use PHPUnit\Framework\Assert;

final class UsersMigration extends AbstractMigration
{
    protected string $table = 'co_users';

    public function insert(
        ?int $id = null,
        int $status = 0,
        ?string $username = null,
        ?string $password = null,
    ) {
        $sql    = "INSERT INTO {$this->table} (
            usr_id, usr_status_flag, usr_username, usr_password
        ) VALUES (
            :id, :status, :username, :password
        )";
        $stmt   = $this->connection->prepare($sql);
        $params = [
            ':id'       => $id,
            ':status'   => $status,
            ':username' => $username,
            ':password' => $password,
        ];
        $result = $stmt->execute($params);
        if (!$result) {
            Assert::fail(
                "Failed to insert id [#$id] into table [$this->table]"
            );
        }

        return $result;
    }
}
