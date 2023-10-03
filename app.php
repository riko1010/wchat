<?php
require_once 'vendor/autoload.php';
require 'assets-php/classes.php';
use Laminas\Config\Config as Config;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
   
   $r->addRoute('GET', '/{queryarg}/{paginationfrom:\d+}-{paginationto:\d+}', ['Controller', 'RouteIndex']);
   $r->addRoute('GET', '/{queryarg}/{paginationfrom:\d+}[-]', ['Controller', 'RouteIndex']);
   
   $r->addRoute('GET', '/{queryarg}[/]', ['Controller', 'RouteIndex']);
   
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $ConfigFile = 'assets-php/settings.php';
        $container = new DI\Container();
        $container->set(
          'Config', \DI\create('Config', [include $ConfigFile, true])
          );
        $container->set(
          'Request', \DI\create('Request', [$vars])
          );
        // ... call $handler with $vars
        $container->call($handler, $vars);
        //$handler($vars);
        break;
}

?>