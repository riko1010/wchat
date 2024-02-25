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


public function RouteANNOTATION(
  \Psr\Container\ContainerInterface $c,  
  ) {
/* instantiation, ordered */
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request');
$db = $c->get('WChat\Database');
$RequestPost = new \WChat\Request($_POST, true);
$Config->InitType = 'ANNOTATION';

/* if request pattern is incorrect */
if (
  !isset($RequestPost->updateannotation) ||
  $RequestPost->updateannotation !== 'yes' || 
  empty($RequestPost->annotation) || 
  $_SESSION['users_id'] == null
  ) {
  print json_encode(['status' => 'error', 'response' => 'error - empty fields']);
  exit;
}

try {
$RawAnnotation = $_POST['annotation'];
require_once '/path/to/HTMLPurifier.auto.php';

$config = \HTMLPurifier_Config::createDefault();
//$config->set('CSS.AllowedProperties', null);
$purifier = new \HTMLPurifier($config);
$clean_html = $purifier->purify($RawAnnotation);

$InsertOrUpdate = $db->InsertOrUpdate(
    'chatfiles',
    [
    'annotation' => $clean_html,
    ],
    [ 
    'id' => $RequestPost->annotationid,
    'users_id' => $_SESSION['users_id'],
    ],
    'update'
  );
  
  if (!$InsertOrUpdate->status) {
    Throw new \Exception ($InsertOrUpdate->response);
  } else {
    print json_encode([
        'status' => 'success',
        'response' => $RequestPost->annotation,
        ]); 
        exit;
  }
  
} catch (\Exception|\Throwable $e) {
  print json_encode([
        'status' => 'error',
        'response' => 'Update failed:'.$e->getMessage()
        ]); 
  exit;      
  }

}

public function RouteADMIN(
  \Psr\Container\ContainerInterface $c,  
  ) {
/* instantiation, ordered */
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request');
$db = $c->get('WChat\Database');
$RequestPost = new \WChat\Request($_POST, true);
$Config->InitType = 'ADMIN';

/* logout */
if (isset($RequestPost->logout) && $RequestPost->logout == 'yes') {
  $_SESSION = [];
  print json_encode(['status' => 'success', 'response' => 'Logged out.']);
  exit;
}

/* upload chatfile */
if (isset($RequestPost->uploadchatfile) && !empty($RequestPost->uploadchatfile)) {
  print json_encode(['status' => 'success', 'response' => 'file upload.']);
  exit;
}

/* login */
if (empty($RequestPost->email) || empty($RequestPost->password)) { print json_encode(['status' => 'error', 'response' => 'Email or Password is empty.']);
  exit;
}

$Request->email = $RequestPost->email;
/* all user inputs are sanitized. special chars in password reasonably gets escaped/stripped using raw post data */
$Request->password = $_POST['password'];

/* if logged in */
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] !== false) {
 /* logged in */
  $AdminLoginQuery = $db->SelectOne(
  'users',
  ['email' => $_SESSION['email'],]
  );
} else {
  /* log in */
$AdminLoginQuery = $db->SelectOne(
  'users',
  [
  'email' => $Request->email,
  'password' => $Request->password
  ]
  );
}
/* login, fetch data, iterable to array */
$AdminLoginQueryData = $AdminLoginQuery->status ? 
  \BenTools\IterableFunctions\iterable_to_array($AdminLoginQuery->response)
  : 
  [];

if (!empty($AdminLoginQueryData)) {
/* assign session data, logged in */
$_SESSION['loggedin'] = true;
$_SESSION['users_id'] = $AdminLoginQueryData[0]['id'];
$_SESSION['email'] = $AdminLoginQueryData[0]['email'];

/* get users chatfiles */
$ChatFilesQuery = $db->Select(
  'chatfiles',
  [
  'users_id' => $_SESSION['users_id'],
  ],
  ['dateadded DESC']
  );
/* chatfiles iterable to array */
$ChatFilesQueryData = $ChatFilesQuery->status ? 
  \BenTools\IterableFunctions\iterable_to_array($ChatFilesQuery->response) 
  : 
  [];
/* login successful, fetch successful, return email, chatfiles */
print json_encode(['status' => 'success', 'response' => $AdminLoginQueryData[0]['email'], 'chatfiles' => $ChatFilesQueryData]);
exit;
}


  /* login failed */
print json_encode(['status' => 'error', 'response' => 'Email or Password is incorrect.']);

/*
$Init = $c->get('WChat\Init');
$App = $c->get('WChat\App');

$c->call(['WChat\processLines', 'ProcessSearchAndPrint']);
*/

}

public function RouteIFRAMES(
  \Psr\Container\ContainerInterface $c,  
  ) {
/* instantiation, ordered, some classes are unecessary for file upload */
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request');
$db = $c->get('WChat\Database');

$Init = $c->get('WChat\Init');
$sitemap = $c->get('WChat\generateSiteMap');
$App = $c->get('WChat\App');

$RequestR = new \WChat\Request($_REQUEST, true);
$Config->InitType = 'IFRAMES';

/* upload form */
if (isset($RequestR->uploadform) && $RequestR->uploadform == 'show') {
$loader = new \Twig\Loader\FilesystemLoader(pj($Config->baseDir, 'assets-templates'));
/* idk why caching was disabled */
//'cache' => pj($Config->baseDir, '/assets-templates/cache'),
$twig = new \Twig\Environment($loader, [
  'cache' => false,
]);

print $twig->render('uploadchatfiles.twig', [
    'App' => $App,
    'Config' => $Config,
    'session' => $_SESSION,
    'container' => $c,
  ]);
  exit;
}

/* upload chatfile */
if (isset($RequestR->uploadchatfile) && !empty($RequestR->uploadchatfile)) {
  print json_encode(['status' => 'success', 'response' => 'file upload.']);
  exit;
}

}

}
?>