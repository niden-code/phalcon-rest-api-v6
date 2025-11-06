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

namespace Phalcon\Api\Domain\Components\DataSource\User;

use Phalcon\Api\Domain\Components\DataSource\AbstractRepository;
use Phalcon\Api\Domain\Components\Enums\Common\FlagsEnum;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Update;

/**
 * @phpstan-import-type TUserRecord from UserTypes
 * @phpstan-import-type TUserInsert from UserTypes
 * @phpstan-import-type TUserUpdate from UserTypes
 *
 * The 'final' keyword was intentionally removed from this class to allow
 * extension for testing purposes (e.g., mocking in unit tests).
 *
 * Please avoid extending this class in production code unless absolutely necessary.
 */
class UserRepository extends AbstractRepository
{
    /**
     * @var string
     */
    protected string $idField = 'usr_id';
    /**
     * @var string
     */
    protected string $table = 'co_users';

    /**
     * @param string $email
     *
     * @return TUserRecord
     */
    public function findByEmail(string $email): array
    {
        $result = [];
        if (true !== empty($email)) {
            return $this->findOneBy(
                [
                    'usr_email'       => $email,
                    'usr_status_flag' => FlagsEnum::Active->value,
                ]
            );
        }

        return $result;
    }

    /**
     * @param TUserInsert $userData
     *
     * @return int
     */
    public function insert(array $userData): int
    {
        $createdUserId = $userData['createdUserId'];
        $updatedUserId = $userData['updatedUserId'];

        $columns = [
            'usr_status_flag'    => $userData['status'],
            'usr_email'          => $userData['email'],
            'usr_password'       => $userData['password'],
            'usr_name_prefix'    => $userData['namePrefix'],
            'usr_name_first'     => $userData['nameFirst'],
            'usr_name_middle'    => $userData['nameMiddle'],
            'usr_name_last'      => $userData['nameLast'],
            'usr_name_suffix'    => $userData['nameSuffix'],
            'usr_issuer'         => $userData['issuer'],
            'usr_token_password' => $userData['tokenPassword'],
            'usr_token_id'       => $userData['tokenId'],
            'usr_preferences'    => $userData['preferences'],
            'usr_created_date'   => $userData['createdDate'],
            'usr_updated_date'   => $userData['updatedDate'],
        ];

        $insert = Insert::new($this->connection);

        $insert
            ->into($this->table)
            ->columns($columns)
        ;

        if ($createdUserId > 0) {
            $insert->column('usr_created_usr_id', $createdUserId);
        }
        if ($updatedUserId > 0) {
            $insert->column('usr_updated_usr_id', $updatedUserId);
        }

        $insert->perform();

        return (int)$insert->getLastInsertId();
    }

    /**
     * @param TUserUpdate $userData
     *
     * @return int
     */
    public function update(array $userData): int
    {
        $userId        = $userData['id'];
        $updatedUserId = $userData['updatedUserId'];

        $columns = [
            'usr_status_flag'    => $userData['status'],
            'usr_email'          => $userData['email'],
            'usr_password'       => $userData['password'],
            'usr_name_prefix'    => $userData['namePrefix'],
            'usr_name_first'     => $userData['nameFirst'],
            'usr_name_middle'    => $userData['nameMiddle'],
            'usr_name_last'      => $userData['nameLast'],
            'usr_name_suffix'    => $userData['nameSuffix'],
            'usr_issuer'         => $userData['issuer'],
            'usr_token_password' => $userData['tokenPassword'],
            'usr_token_id'       => $userData['tokenId'],
            'usr_preferences'    => $userData['preferences'],
            'usr_updated_date'   => $userData['updatedDate'],
        ];

        $update = Update::new($this->connection);
        $update
            ->table($this->table)
            ->columns($columns)
            ->where('usr_id = ', $userId)
        ;

        if ($updatedUserId > 0) {
            $update->column('usr_updated_usr_id', $updatedUserId);
        }

        $update->perform();

        return (int)$userId;
    }
}
