<?php
return [
  'whatsappchatsURI' => 'https://dev.wchat.space',
  'SiteUrl' => 'https://dev.wchat.space',
  'recordsperpage' => '',
  'unzippyURI' => 'https://wchat.space/python/rununzip.py',
  'dropboxfolderuriaszip' => 'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?dl=1',
  'dropboxfolderuriaszipheader' => 'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?raw=1',
  'PyArchiveURI' => '',
  'cfFolder' => '',
  'videoPlayerURL' => '',
  'videoPlayerPoster' => '',
  'docViewer' => '',
  'ogimage' => '',
  'ogcontenttype' => '',
  'cfFilespattern' => '',
  'sitemapxml' => '',
  'robotstxt' => '',
  '$baseDir' => '',
  'sqlitedb' => ''
  ];
  
$whatsappchatsURI = 'https://dev.wchat.space';
/* urls can be same , url below uses this dir as base dir */
$SiteUrl = 'https://dev.wchat.space';
/* record per page */
$recordsperpage = 100;
/* set path to unzip.py unzip, .htaccess in $basedir/python folder to allow execution */
$unzippyURI = 'https://wchat.space/python/rununzip.py';
/* conversations folder zip - .zip/conversations/folders */
$dropboxfolderuriaszip = "https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?dl=1";
//Header URL of the file, dropbox
$dropboxfolderuriaszipheader = "https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?raw=1";
/* archived links with python script */
$PyArchiveURI = 'https://wchat.space/python/runarchive.py';
/* folder with individual chat folders */
$cfFolder = "conversations"; 
$extracted = true;
/* video url as %s , replacement with sprintf */
$videoPlayerURL = 'https://onelineplayer.com/player.html?autoplay=true&autopause=true&muted=true&loop=true&url='
.'%s'.
'&poster=&time=true&progressBar=true&overlay=true&muteButton=true&fullscreenButton=true&style=light&quality=auto&playButton=true';
/* default video poster - if filename.ext.png exists in same dir, it'll be used instead */
$videoPlayerPoster = 'images/videoposter.png';
/* doc url as %s, replacement with sprintf */
$docViewer = 'https://docs.google.com/viewer?url='
.'%s'.
'&embedded=true';
/* opengraph */
$ogImage = $SiteUrl.'/images/ogimage.jpg';
$ogcontenttype = 'chat';
/* matches chatfile.(unique int identifier).txt - last occurence .(unique int identifier).txt */
$cfFilespattern = '/WhatsApp Chat with\s(?P<name>.*?)((\.\d)?.txt)/i';
/* sitemap */
$sitemapxml = 'sitemap.xml';
/* robots.txt */
$robotstxt = 'robots.txt';
/* paths - settings is in assets-php which is one level down from basedir . basedir is determined relative to settings
dirname() of assets-php dir = basedir */
$baseDir = dirname(__DIR__);
/* sqlite */
$sqlitedb = 'db.sqlite';