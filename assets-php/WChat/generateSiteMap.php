<?php
namespace WChat;
class generateSiteMap {
public $FilesandStatus;

public function __construct(
  Config $Config,
  Init $Init,
  \Psr\Container\ContainerInterface $c,
  ){
  
  $this->FilesandStatus = $c->call([$this, 'getSitemap']);
  
}

public function getSitemap(
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get("WChat\Config");
$Init = $c->get("WChat\Init");
$GenerateSitemap = $Init->UpdateSitemap ? true : false;
if ($GenerateSitemap) {
  $Generate = $c->call([$this, 'Generate']); 
  $Responses = 'Generate:'.$Generate->response;
  $sitemap = [ 
    'status' => true,
    'response' => 'Sitemap listed.'.$Responses
    ];
} else {
  /* current trigger is updatefilesystemfromdb, considering recommedations for sitemap udpate, content update(covered by updatefilesystemfromdb) */
  $sitemap = [ 
    'status' => true,
    'response' => 'Sitemap listed.'
    ];
}
//objects 
$files = $this->filesExists(
  $Config->baseDir,
  $Config->sitemapxml, 
  $Config->robotstxt,
);

return (object) [
  'sitemap' => (object) $sitemap,
  'files' => $files
  ];
}

public function filesExists($sdir, ...$sfiles){
$data = [];
foreach ($sfiles as $sfile) {
settype($sfile, 'string');
clearstatcache();
$abspath = $sfile;
$data[] = [
  'filename' => $sfile,
  'rpath' => \Symfony\Component\Filesystem\Path::makeRelative($abspath, $sdir),
  'exists' => (($fe = file_exists($abspath)) ? true : false),
  'stats' => ($fe ? stat($abspath) : false)
  ];
}
return $data;
}

public function Generate(
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get("WChat\Config");
$Init = $c->get("WChat\Init");
if (!is_dir('autodelete')) {
  if (!mkdir('autodelete')) {
    return (object) [
    'status' => false,
    'response' => 'cannot create auto delete folder'
    ];
  }
}

if (file_exists($Config->sitemapxml)) {
clearstatcache();  
  if (!rename($Config->sitemapxml, \Symfony\Component\Filesystem\Path::join('autodelete', $Config->sitemapxml))) { 
    return (object) [
    'status' => false,
    'response' => 'cannot move '.$Config->sitemapxml.' to autodelete'
    ];
  }
}

try {
// create Sitemap
$sitemap = new \samdark\sitemap\Sitemap($Config->sitemapxml);
} catch(Exception $e) {
  return [
    'status' => false,
    'response' => 'error: samdark/sitemap new Sitemap error'
    ];
}

$ChatFilesData_column_search = array_column(
  $Init->Data->Data, 
  'url'
  );
foreach ($ChatFilesData_column_search as $Links) {
$Link = $Links;
// add some URLs to sitemap
try {
$sitemap->addItem($Link, time()); 
} catch (Exception $e) {
  //archive links error
}
}

try {
$sitemap->setStylesheet(
  $Init->addurl(
    'sitemap.xsl', 
    $Config, 
    ));
$sitemap->write();
} catch(Exception $e){
  //sitemap.xml write error
  return (object) [
    'status' => false,
    'response' => $Config->sitemapxml.' - cant update(3)'
    ];
}

if (file_exists($Config->sitemapxml)) {
  clearstatcache();

} else {
  /* failed to generate sitemap, restore old */
  if (fie_exists(Path::join('autodelete', $Config->sitemapxml))) {
    clearstatcache();
    rename(Path::join('autodelete', $Config->sitemapxml), $Config->sitemapxml);
  return (object) [
    'status' => false,
    'response' => 'sitemap generator failed - old sitemap restored'
    ];
  }
}

return (object) [
    'status' => true,
    'response' => 'Sitemap generated.'
    .'\n'
    ];
/* end sitemap generator function */
}

}