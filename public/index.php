<?php

declare(strict_types=1);

use Phalcon\Api\Domain\Interfaces\DomainInterface;
use Phalcon\Api\Domain\Interfaces\ResponderInterface;
use Phalcon\Api\Domain\Middleware\ResponseSender;
use Phalcon\Api\Domain\Services\ActionHandler;
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
        'method'    => 'get',
        'pattern'   => '/',
        'service'   => Container::HELLO_SERVICE,
        'responder' => Container::HELLO_RESPONDER_JSON,
    ],
];

foreach ($routes as $route) {
    $method        = $route['method'];
    $pattern       = $route['pattern'];
    $serviceName   = $route['service'];
    $responderName = $route['responder'];

    $application->$method(
        $pattern,
        function () use ($container, $serviceName, $responderName) {
            /** @var DomainInterface $service */
            $service   = $container->get($serviceName);
            /** @var ResponderInterface $responder */
            $responder = $container->get($responderName);

            $action = new ActionHandler($service, $responder);
            $action->__invoke();
        }
    );
}

$application->finish(
    function () use ($container) {
        $response = $container->getShared(Container::RESPONSE);
        $sender   = new ResponseSender();

        $sender->__invoke($response);
    }
);

$application->notFound(
    function () {
        echo "404 - Not Found - " . date("Y-m-d H:i:s");
    }
);


/** @var string $uri */
$uri = $_SERVER['REQUEST_URI'] ?? '';

$application->handle($uri);
