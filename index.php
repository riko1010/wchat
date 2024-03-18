<?php
error_reporting(0);
session_name('wchat');
session_start();
$_SESSION['statusconsole'] = [];
/* $_SESSION['statusconsole'][] = newval; */

require_once 'vendor/autoload.php';
require 'assets-php/Functions.php';
use Psr\Container\ContainerInterface;
use DI\Container;
use SoftCreatR\MimeDetector\MimeDetector;
use SoftCreatR\MimeDetector\MimeDetectorException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$Dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
   
   $r->addRoute(['POST', 'GET'], '/annotation[/]', ['WChat\Controller', 'RouteANNOTATION']);
   
   $r->addRoute(['POST', 'GET'], '/iframes[/]', ['WChat\Controller', 'RouteIFRAMES']);
   
   $r->addRoute('POST', '/admin[/]', ['WChat\Controller', 'RouteADMIN']);
   
   $r->addRoute('GET', '/api[/]', ['WChat\Controller', 'RouteAPI']);
   
   $r->addRoute('GET', '/{queryarg}/{paginationfrom:\d+}[/]', ['WChat\Controller', 'RouteCHATFILE']);
   
   $r->addRoute('GET', '/{queryarg}[/]', ['WChat\Controller', 'RouteCHATFILE']);
   
   $r->addRoute('GET', '/{queryarg:.+}', ['WChat\Controller', 'RouteCHATFILE']);
   
   $r->addRoute('GET', '/', ['WChat\Controller', 'RouteDASHBOARD']);
});

// Fetch method and URI from somewhere
$Route = new WChat\Router;
$Route->httpMethod = $_SERVER['REQUEST_METHOD'];
$Route->httpURI = $Route->RelativeURI($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']);
$routeInfo = $Route->Info($Dispatcher);

switch ($routeInfo->Dispatcher) {
case FastRoute\Dispatcher::NOT_FOUND:
  // ... 404 Not Found
  break;
case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
  $allowedMethods = $routeInfo->Handler;
  // ... 405 Method Not Allowed
  break;
case FastRoute\Dispatcher::FOUND:
  $ConfigFile = include 'assets-php/settings.php';
  $builder = new \DI\ContainerBuilder();
  $builder->addDefinitions([
  'InitType' => 'Index',
  'REQUEST_URI' => $_SERVER['REQUEST_URI'],
  'RequestRaw' => $routeInfo->RequestRaw,
  'Config.ConfigFile' => $ConfigFile,
  ]);
  $builder->addDefinitions('assets-php/Definitions.php');
  $container = $builder->build();
  // ... call $Handler with $vars
  
 // $container->get('WChat\Config');
  //$container->get('WChat\Request');
  $container->call($routeInfo->Handler);
  break;
}

?>