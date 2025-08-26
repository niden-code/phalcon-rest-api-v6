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


use Phalcon\Api\Domain\Services\Container;
use Phalcon\Api\Domain\Services\Environment\EnvManager;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container();

///** @var array<array-key, string> $providers */
//$providers = require_once EnvManager::appPath('/config/providers.php');

//$application = new Api($container, $providers);
//
//$application->setup()->run();
