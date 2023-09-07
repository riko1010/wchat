<?php

$whatsappchatsURI = 'https://trustedlinks.site/whatsappchats';
/* urls can be same , url below uses this dir as base dir */
$SiteUrl = 'https://wchat.space';
/* archived links with python script */
$PyArchiveURI = 'https://trustedlinks.site/cgi-bin/runarchive.py';
/* archived links */
$archivedsitemap = 'archivedsitemap.txt';
/* folder with individual chat folders */
$cfFolder = "conversations"; 
$generatesitemapfile = 'generate_sitemap';
$extracted = true;
$sendermail = 'archivelinks@wchat.space';
$smtp = array(
  'server' => 'wchat.space', 
  'port' => '465',
  'username' => 'archivelinks@wchat.space',
  'password' => 'bx(3(tL!iG;Z',
  'secure' => 'ssl',
  'bcc' => 'archivelinks@wchat.space'
  );
$archiveMails = array(
  'savepagenow@archive.org',
  'spn@archive.org'
  );
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
/* url in string parser, top level tlds in links without protocol, replacement pattern, current is phug. url is %s */
$UrlHighlight = 'a(href="%s")%s/';
/* matches chatfile.(unique int identifier).txt - last occurence .(unique int identifier).txt */
$cfFilespattern = '/WhatsApp Chat with\s(?P<name>.*?)((\.\d)?.txt)/i';
/* sitemap */
$sitemapxml = 'sitemap.xml';
$sitemaptxt = 'sitemap.txt';
$sitemapcsv = 'sitemap.csv';
/* robots.txt */
$robotstxt = 'robots.txt';