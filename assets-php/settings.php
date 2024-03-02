<?php
/* removed pyarchiveurl
adding mail/mail archive config */
return [
  'whatsappchatsURI' => 'https://wchat.space',
  'SiteUrl' => 'https://wchat.space',
  'APIUrl' => 'https://wchat.space/api/', 
  'ADMINUrl' => 'https://wchat.space/admin/', 
  'IFRAMESUrl' => 'https://wchat.space/iframes/', 
  'ANNOTATIONUrl' => 'https://wchat.space/annotation/', 
  'PaginationFrom' => 0,
  'recordsperpage' => 100,
  'unzippyURI' => 'https://wchat.space/python/rununzip.py',
  'dropboxfolderuriaszip' =>
    'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?dl=1',
  'dropboxfolderuriaszipheader' =>
    'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?raw=1',
  'cfFolder' => 'conversations',
  'videoPlayerURL' =>
    'https://onelineplayer.com/player.html?autoplay=true&autopause=true&muted=true&loop=true&url=%s&poster=&time=true&progressBar=true&overlay=true&muteButton=true&fullscreenButton=true&style=light&quality=auto&playButton=true',
  'videoPlayerPoster' => 'images/videoposter.png',
  'docViewer' => 'https://docs.google.com/viewer?url=%s&embedded=true',
  'og' => [
    'image' => 'https://wchat.space/images/ogimage.jpg',
    'contenttype' => 'chat',
  ],
  'cfFilespattern' => '/WhatsApp Chat with\s(?P<name>.*?)((\.\d)?.txt)/i',
  'sitemapxml' => 'sitemap.xml',
  'robotstxt' => 'robots.txt',
  'baseDir' => dirname(__DIR__),
  'sqlitedb' => '/home/badlnykl/assets-intranet' . '/' . 'db.sqlite',
  'SuggestGroupChat' => true,
  'Mail' => [
    'Type' => 'sendmail',
    'FromName' => 'wchat.space',
    'FromEmail' => 'archive@wchat.space',
    'cc' => '',
    'bcc' => '',
    ],
  'ArchiveMailAddresses' => [
 /* 'spn@archive.org', */
  ],
];
