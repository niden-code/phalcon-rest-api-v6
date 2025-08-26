<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCompaniesTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'co_companies',
            [
                'id'     => 'com_id',
                'signed' => false,
            ]
        );

        $table
            ->addColumn(
                'com_name',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'com_address',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'com_city',
                'string',
                [
                    'limit'   => 64,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'com_telephone',
                'string',
                [
                    'limit'   => 24,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'com_created_date',
                'datetime',
                [
                    'null'    => true,
                    'default' => 'CURRENT_TIMESTAMP',
                ]
            )
            ->addColumn(
                'com_created_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'com_updated_date',
                'datetime',
                [
                    'null' => true,
                ]
            )
            ->addColumn(
                'com_updated_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addIndex('com_name')
            ->addIndex('com_city')
            ->addIndex('com_created_date')
            ->save();

        $this->execute(
            'ALTER TABLE co_companies ' .
            'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        $this->table('co_companies')->drop()->save();
    }
}
