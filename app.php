<?php
require_once 'vendor/autoload.php';
require 'assets-php/classes.php';

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
        $ConfigFile = 'assets-php/settings.php';
        $Config = new Config (include $ConfigFile, true);
        $container = new DI\Container();
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        var_dump($_REQUEST);
        exit;
        // ... call $handler with $vars
        $container->call($handler, $vars);
        var_dump($vars);
        //$handler($vars);
        break;
}

?>