<?php

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
        'handler' => Container::HELLO_ACTION,
    ],
];

foreach ($routes as $route) {
    $method  = $route['method'];
    $pattern = $route['pattern'];
    $handler = $route['handler'];

    $application->$method(
        $pattern,
        function () use ($container, $handler) {
            $action = $container->get($handler);

            $action();
        }
    );
}

$application->notFound(
    function () {
        echo "404 - Not Found - " . date("Y-m-d H:i:s");
    }
);


/** @var string $uri */
$uri = $_SERVER['REQUEST_URI'] ?? '';

$application->handle($uri);
