<?php
return [
  'whatsappchatsURI' => 'https://dev.wchat.space',
  'SiteUrl' => 'https://dev.wchat.space',
  'recordsperpage' => 100,
  'unzippyURI' => 'https://wchat.space/python/rununzip.py',
  'dropboxfolderuriaszip' => 'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?dl=1',
  'dropboxfolderuriaszipheader' => 'https://www.dropbox.com/sh/bw5ze45z2essn97/AADWfIO9D7riaye8WwI9mDyNa?raw=1',
  'PyArchiveURI' => 'https://wchat.space/python/runarchive.py',
  'cfFolder' => 'conversations',
  'videoPlayerURL' => 'https://onelineplayer.com/player.html?autoplay=true&autopause=true&muted=true&loop=true&url=%s&poster=&time=true&progressBar=true&overlay=true&muteButton=true&fullscreenButton=true&style=light&quality=auto&playButton=true',
  'videoPlayerPoster' => 'images/videoposter.png',
  'docViewer' => 'https://docs.google.com/viewer?url=%s&embedded=true',
  'og' => [
    'image' => 'https://dev.wchat.space/images/ogimage.jpg',
    'contenttype' => 'chat'
    ],
  'cfFilespattern' => '/WhatsApp Chat with\s(?P<name>.*?)((\.\d)?.txt)/i',
  'sitemapxml' => 'sitemap.xml',
  'robotstxt' => 'robots.txt',
  'baseDir' => dirname(__DIR__),
  'sqlitedb' => dirname(__DIR__).'db.sqlite'
  ];
 