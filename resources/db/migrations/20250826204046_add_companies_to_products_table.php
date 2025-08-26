<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCompaniesToProductsTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'co_companies_x_products',
            [
                'id'          => false,
                'primary_key' => [
                    'cxp_com_id',
                    'cxp_prd_id',
                ],
            ]
        );

        $table
            ->addColumn(
                'cxp_com_id',
                'integer',
                [
                    'signed'  => false,
                    'limit'   => 11,
                    'null'    => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'cxp_prd_id',
                'integer',
                [
                    'signed'  => false,
                    'limit'   => 11,
                    'null'    => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'cxp_created_date',
                'datetime',
                [
                    'null'    => true,
                    'default' => 'CURRENT_TIMESTAMP',
                ]
            )
            ->addColumn(
                'cxp_created_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'cxp_updated_date',
                'datetime',
                [
                    'null' => true,
                ]
            )
            ->addColumn(
                'cxp_updated_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addIndex('cxp_com_id')
            ->addIndex('cxp_prd_id')
            ->save();

        $this->execute(
            'ALTER TABLE co_companies_x_products ' .
            'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        $this->table('co_companies_x_products')->drop()->save();
    }
}
