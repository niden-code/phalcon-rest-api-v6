<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUsersTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'co_users',
            [
                'id'     => 'usr_id',
                'signed' => false,
            ]
        );

        $table
            ->addColumn(
                'usr_status_flag',
                'boolean',
                [
                    'signed'  => false,
                    'null'    => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'usr_username',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'usr_password',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'usr_issuer',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'usr_token_id',
                'string',
                [
                    'limit'   => 256,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'usr_token_password',
                'string',
                [
                    'limit'   => 256,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'usr_created_date',
                'datetime',
                [
                    'null'    => true,
                    'default' => 'CURRENT_TIMESTAMP',
                ]
            )
            ->addColumn(
                'usr_created_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'usr_updated_date',
                'datetime',
                [
                    'null' => true,
                ]
            )
            ->addColumn(
                'usr_updated_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addIndex('usr_status_flag')
            ->addIndex('usr_username')
            ->addIndex('usr_token_id', ['unique' => true])
            ->addIndex('usr_created_date')
            ->save();

        $this->execute(
            'ALTER TABLE co_users ' .
            'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        $this->table('co_users')->drop()->save();
    }
}
