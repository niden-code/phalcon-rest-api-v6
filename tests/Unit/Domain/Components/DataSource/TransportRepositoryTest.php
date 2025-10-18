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

namespace Phalcon\Api\Tests\Unit\Domain\Components\DataSource;

use Phalcon\Api\Domain\Components\Container;
use Phalcon\Api\Domain\Components\DataSource\TransportRepository;
use Phalcon\Api\Tests\AbstractUnitTestCase;

final class TransportRepositoryTest extends AbstractUnitTestCase
{
    public function testNewUser(): void
    {
        /** @var TransportRepository $transport */
        $transport = $this->container->get(Container::REPOSITORY_TRANSPORT);

        $user   = $transport->newUser([]);
        $actual = $user->isEmpty();
        $this->assertTrue($actual);

        $dbUser = $this->getNewUserData();
        $user   = $transport->newUser($dbUser);

        $fullName = trim(
            $dbUser['usr_name_last']
            . ', '
            . $dbUser['usr_name_first']
            . ' '
            . $dbUser['usr_name_middle']
        );

        $expected = [
            $dbUser['usr_id'] => [
                'id'            => $dbUser['usr_id'],
                'status'        => $dbUser['usr_status_flag'],
                'email'         => $dbUser['usr_email'],
                'password'      => $dbUser['usr_password'],
                'namePrefix'    => $dbUser['usr_name_prefix'],
                'nameFirst'     => $dbUser['usr_name_first'],
                'nameMiddle'    => $dbUser['usr_name_middle'],
                'nameLast'      => $dbUser['usr_name_last'],
                'nameSuffix'    => $dbUser['usr_name_suffix'],
                'issuer'        => $dbUser['usr_issuer'],
                'tokenPassword' => $dbUser['usr_token_password'],
                'tokenId'       => $dbUser['usr_token_id'],
                'preferences'   => $dbUser['usr_preferences'],
                'createdDate'   => $dbUser['usr_created_date'],
                'createdUserId' => $dbUser['usr_created_usr_id'],
                'updatedDate'   => $dbUser['usr_updated_date'],
                'updatedUserId' => $dbUser['usr_updated_usr_id'],
                'fullName'      => $fullName,
            ],
        ];
        $actual   = $user->toArray();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_id'];
        $actual   = $user->getId();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_status_flag'];
        $actual   = $user->getStatus();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_email'];
        $actual   = $user->getEmail();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_password'];
        $actual   = $user->getPassword();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_prefix'];
        $actual   = $user->getNamePrefix();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_first'];
        $actual   = $user->getNameFirst();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_middle'];
        $actual   = $user->getNameMiddle();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_last'];
        $actual   = $user->getNameLast();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_name_suffix'];
        $actual   = $user->getNameSuffix();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_issuer'];
        $actual   = $user->getIssuer();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_password'];
        $actual   = $user->getTokenPassword();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_token_id'];
        $actual   = $user->getTokenId();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_preferences'];
        $actual   = $user->getPreferences();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_date'];
        $actual   = $user->getCreatedDate();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_created_usr_id'];
        $actual   = $user->getCreatedUserId();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_date'];
        $actual   = $user->getUpdatedDate();
        $this->assertSame($expected, $actual);

        $expected = $dbUser['usr_updated_usr_id'];
        $actual   = $user->getUpdatedUserId();
        $this->assertSame($expected, $actual);

        $expected = $fullName;
        $actual   = $user->getFullName();
        $this->assertSame($expected, $actual);
    }
}
