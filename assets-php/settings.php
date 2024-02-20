<?php
/* removed pyarchiveurl
adding mail/mail archive config */
return [
  'whatsappchatsURI' => 'https://dev.wchat.space',
  'SiteUrl' => 'https://dev.wchat.space',
  'APIUrl' => 'https://dev.wchat.space/api/', 
  'ADMINUrl' => 'https://dev.wchat.space/admin/', 
  'IFRAMESUrl' => 'https://dev.wchat.space/iframes/', 
  'PaginationFrom' => 0,
  'recordsperpage' => 100,
  'unzippyURI' => 'https://dev.wchat.space/python/rununzip.py',
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
    'image' => 'https://dev.wchat.space/images/ogimage.jpg',
    'contenttype' => 'chat',
  ],
  'cfFilespattern' => '/WhatsApp Chat with\s(?P<name>.*?)((\.\d)?.txt)/i',
  'sitemapxml' => 'sitemap.xml',
  'robotstxt' => 'robots.txt',
  'baseDir' => dirname(__DIR__),
  'sqlitedb' => '/home/badlnykl/assets-intranet' . '/' . 'db-dev.sqlite',
  'SuggestGroupChat' => true,
  'Mail' => [
    'Type' => 'sendmail',
    'FromName' => 'dev.wchat.space',
    'FromEmail' => 'archive@dev.wchat.space',
    'cc' => '',
    'bcc' => '',
    ],
  'ArchiveMailAddresses' => [
 /* 'spn@archive.org', */
  ],
];
