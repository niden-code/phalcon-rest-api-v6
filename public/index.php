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

use Phalcon\Api\Domain\Services\Container;
use Phalcon\Mvc\Micro;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$container   = new Container();
$application = new Micro($container);

/**
 * Routes
 */
$routes = [
    [
        'method'  => 'get',
        'pattern' => '/',
        'handler' => Container::HELLO_ACTION_GET,
    ]
];

foreach ($routes as $route) {
    $method  = $route['method'];
    $pattern = $route['pattern'];
    $handler = $route['handler'];

    $application->$method(
        $pattern,
        function () use ($container, $handler) {
            $action = $container->get($handler);

            echo $action->execute();
        }
    );
}

$application->notFound(
    function () {
        echo '404 - Not Found';
    }
);


/** @var string $uri */
$uri = $_SERVER['REQUEST_URI'] ?? '';

$application->handle($uri);
