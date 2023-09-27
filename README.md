# Readme Guide
0.0.3-0.0.3-dev

## Install
config in assets-php/settings.php
conversations folder holds chat folders containing chat files and media
dropbox can host conversations folder which is downloaded to local conversations folder on content change. You should create or configure a dropbox app with dropbox.php as the webhook url for changes in your Dropbox account. when the webhook is sent, a check for folder size change will occur at the provided dropbox folder zip download link {$dropboxfolderuriaszip}, if change in last fetched size stored using lazerdb(flat file db) a download will occur replacing conversations folder(assumption is dropbox folder is mirrored to local conversations folder).
placing chat folders in conversations would work fine, extensionless files from whatsapp exports will be fixed.
most services will be removed for local rendering, if you wish change the services to preferred alternatives.


php and python
works without python, php zip renames files ending with '.' to _, python unzips fine so unzip of dropbox conversations folder fetched on webhook notice happens with python. Will consider a php unzip to reduce dependence on python.


python config in python/archive.py , python/unzip.py - will centralize config btw php and python
- python handles unzip
- python handles fixing extensionless files, identifying file extension, renaming with proper extension and fixing references to files in chatfiles.
- python handles archiving with pypi/waybackpy
- python handles screnshot of video loaded using a webvideo player then screenshot using a browserscreenshot service.

## feature list
-  \+,_ removal in international numbers or any url path
-  multiple chat files in one folder (naming conflict resolution by filename.{int}.txt )
-  attachment identifiers (images,doc,media)
-  sitemap generation (xml) on conversations folder modification through dropbox webhook
-  archive through archive.org spn (pypi/waybackpy), sitemap generation trigges archive, links are from sitemap.csv(updated) ( takes 6 mins each url when throttled ) - sitemap.csv maintains archived links to avoid duplication.
-  extensionless file fix (filename.) through unzip.py on webhook notice of folder change from dropbox, and, php, on requesting chatfile
-  pyscripts in python , .htaccess enables cgiexec in python
-  api for pagination in basedir/api
-  sqlite for db, db & tables are created on init, updated on directory modification
  
## todo
-  unreachable python urls will fail silently
-  context menu, floating menu, url, archive.org url, permanent shortlink
-  permanent shortlink to archive.org spn (may use cors requests hack, desire for unmodifiable shortlinks, current options allow modification when using api)
-  generate sitemap.xml, through unzip.py
-  error logger - whoops to slack, mail, other, loggly, telegram,
-  python singleton ? maxtime exec independent of singleton
-  external services removal for local rendering, google docs viewer, others
-  centralize config btw php and python