<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProductsTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'co_products',
            [
                'id'     => 'prd_id',
                'signed' => false,
            ]
        );

        $table
            ->addColumn(
                'prd_name',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'prd_description',
                'string',
                [
                    'limit'   => 256,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'prd_quantity',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'prd_price',
                'decimal',
                [
                    'precision' => 10,
                    'scale'     => 2,
                    'null'      => false,
                    'signed'    => false,
                    'default'   => 0,
                ]
            )
            ->addColumn(
                'prd_created_date',
                'datetime',
                [
                    'null'    => true,
                    'default' => 'CURRENT_TIMESTAMP',
                ]
            )
            ->addColumn(
                'prd_created_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'prd_updated_date',
                'datetime',
                [
                    'null' => true,
                ]
            )
            ->addColumn(
                'prd_updated_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addIndex('prd_name')
            ->addIndex('prd_quantity')
            ->addIndex('prd_price')
            ->save();

        $this->execute(
            'ALTER TABLE co_products ' .
            'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        $this->table('co_products')->drop()->save();
    }
}
