<?php
namespace WChat;
class Controller {

public function RouteCHATFILE(
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

print $twig->render('chatfile.twig', [
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
//$App->GroupChat = true;

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
/* sanitize using HTMLPurifier */
$config = \HTMLPurifier_Config::createDefault();
/* remove css properties to prevent malicious annotations */
$config->set('CSS.AllowedProperties', '');
$purifier = new \HTMLPurifier($config);
$RawAnnotation = $purifier->purify($RawAnnotation);

$InsertOrUpdate = $db->InsertOrUpdate(
    'chatfiles',
    [
    'annotation' => $RawAnnotation,
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
        'response' => $RawAnnotation,
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

/* upload chatfile */
if (empty($_SESSION['users_id'])) {
  print json_encode(['status' => 'error', 'response' => 'You\'re not logged in']);
  exit;
}

/* upload chatfile */

if (isset($RequestR->submitupload) && $RequestR->submitupload == 'true') {
  if (isset($_FILES["chatfilearchive"]) && file_exists($_FILES["chatfilearchive"]["tmp_name"])) 
  {
  print json_encode(['status' => 'success', 'response' => 'file upload done']);
  
  print '<hr/>';
  $_FILES["chatfilearchive"]["name"];
  $_FILES["chatfilearchive"]["tmp_name"];
  
  $zip_file = $_FILES["chatfilearchive"]["tmp_name"];
//  '/path/to/file.zip'; 
// I wan to get stream a CSV files

$zip = new \ZipArchive();
$zip->open($zip_file);
$unsupportedfiletypes = [];
$supportedfiletypes = [];
$targetfolder = $Config->cfFolder;
$foldername = $_POST['foldername'];
$altfoldername = $foldername.''.time();
if (!is_dir(\pj($Config->baseDir, $targetfolder, $foldername))) {
  $foldername = \pj($Config->baseDir,
  $targetfolder
  ,$foldername);
  mkdir($foldername);
} 
/* if folder exists, alt folder name*/
if (!is_dir($foldername)) {
  $foldername = \pj($Config->baseDir, $targetfolder ,$altfoldername);
  mkdir($foldername);  
}
if (!is_dir($foldername)) {
  print json_encode(['status' => 'error', 'response' => 'cannot create main or alt directory']);
  exit;
}
for ($i = 0; $i < $zip->numFiles; $i++) {
// Check file by file
    $name = $zip->getNameIndex($i);
    // Retrieve entry name
    // detect mime type and extention with a good library asap 
    
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    /* accepting extensionless files */
    switch ($extension) {
    case 'txt':
    case 'png':
    case 'jpg':
    case 'jpeg':
    case 'gif':  
    case 'opus':
    case 'mp4':  
    case 'pdf':  
    case 'doc': 
    case 'aac':
    case 'vcf':
    case '':
    case 'docx': $zip->extractTo($foldername, $name);
    $supportedfiletypes[] = $name;
    break;
    default: $unsupportedfiletypes[] = 'unsupported file type:'.$name.' '.$extension;
    }
    
}
print 'uploaded files<br/>';
foreach ($supportedfiletypes as $supportedfiles) {
 print $supportedfiles.'<br/>';
}

print 'unsupported files<br/>';
foreach ($unsupportedfiletypes as $unsupportedfiles) {
  print $unsupportedfiles.'<br/>';;
}


$client = new \Curl\Client();
// returns standardized Response object no matter what
$response = $client->get(\pj($Config->SiteUrl,'python/runfixchatfiles.py'));
$status = $response->status;

if ($status == '200') {
  print 'Chat files fixed';
} else {
  print 'Chat files were not fixed';
}
print '<hr/>';
}
}

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
  
}

}


public function RouteDASHBOARD(
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
$Config->InitType = 'SEARCH';

$loader = new \Twig\Loader\FilesystemLoader(pj($Config->baseDir, 'assets-templates'));
/* idk why caching was disabled */
//'cache' => pj($Config->baseDir, '/assets-templates/cache'),
$twig = new \Twig\Environment($loader, [
  'cache' => false,
]);

print $twig->render('dashboard.twig', [
    'App' => $App,
    'Config' => $Config,
    'session' => $_SESSION,
    'container' => $c,
    'Init' => $Init,
    'RequestR' => $RequestR,
  ]);
  

}

}
?>