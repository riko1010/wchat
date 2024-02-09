<?php
namespace WChat;
class Controller {

public function RouteIndex(
  \Psr\Container\ContainerInterface $c,  
  ) {
/* instantiation, ordered */
$Config = $c->get('WChat\Config');
$Init = $c->get('WChat\Init');
$sitemap = $c->get('WChat\generateSiteMap');
$App = $c->get('WChat\App');

$loader = new \Twig\Loader\FilesystemLoader(pj($Config->baseDir, 'assets-templates'));
/* idk why caching was disabled */
//'cache' => pj($Config->baseDir, '/assets-templates/cache'),
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);
$function = new \Twig\TwigFunction('getFilesize', function (...$args) {
    return \getFilesize(...$args);
});
$twig->addFunction($function);
$function = new \Twig\TwigFunction('screenshot', function ($screenshotapi, $pageuri) {
    return sprintf($screenshotapi, urlencode($pageuri),);
});
$twig->addFunction($function);

print $twig->render('index.twig', [
    'App' => $App,
    'Config' => $Config,
    'Init' => $Init,
    'sitemap' => $sitemap,
    'session' => $_SESSION,
    'container' => $c,
  ]);

}

public function RouteAPI(
  \Psr\Container\ContainerInterface $c,  
  ) {
/* instantiation, ordered */
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request');
$RequestGet = new \WChat\Request($_GET, true);
$Config->InitType = 'API';
/* terminate if no queryarg */
if (empty($RequestGet->queryarg)) exit('Chat ID is empty.');

$Request->queryarg = $RequestGet->queryarg;
$Request->paginationfrom = $RequestGet->paginationfrom;
$Request->needle = $RequestGet->needle;

$Init = $c->get('WChat\Init');
$App = $c->get('WChat\App');
/* groupchat displays both sender names by default, prior to search styling similar to whatsapp styling */
$App->GroupChat = true;

if ($App->NoSelected) exit('Chat ID not found. ');

$c->call(['WChat\processLines', 'ProcessSearchAndPrint']);

}
}
?>