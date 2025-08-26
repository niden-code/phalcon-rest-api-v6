<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIndividualsTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table(
            'co_individuals',
            [
                'id'     => 'ind_id',
                'signed' => false,
            ]
        );
        $table
            ->addColumn(
                'ind_ind_id',
                'integer',
                [
                    'signed'  => false,
                    'limit'   => 11,
                    'null'    => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'ind_idt_id',
                'integer',
                [
                    'signed'  => false,
                    'limit'   => 11,
                    'null'    => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'ind_name_prefix',
                'string',
                [
                    'limit'   => 16,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'ind_name_first',
                'string',
                [
                    'limit'   => 64,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'ind_name_middle',
                'string',
                [
                    'limit'   => 64,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'ind_name_last',
                'string',
                [
                    'limit'   => 128,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'ind_name_suffix',
                'string',
                [
                    'limit'   => 16,
                    'null'    => false,
                    'default' => '',
                ]
            )
            ->addColumn(
                'ind_created_date',
                'datetime',
                [
                    'null'    => true,
                    'default' => 'CURRENT_TIMESTAMP',
                ]
            )
            ->addColumn(
                'ind_created_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                'ind_updated_date',
                'datetime',
                [
                    'null' => true,
                ]
            )
            ->addColumn(
                'ind_updated_usr_id',
                'integer',
                [
                    'limit'   => 11,
                    'null'    => false,
                    'signed'  => false,
                    'default' => 0,
                ]
            )
            ->addIndex('ind_ind_id')
            ->addIndex('ind_idt_id')
            ->addIndex('ind_name_first')
            ->addIndex('ind_name_middle')
            ->addIndex('ind_name_last')
            ->save();

        $this->execute(
            'ALTER TABLE co_individuals ' .
            'CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    public function down()
    {
        $this->table('co_individuals')->drop()->save();
    }
}
