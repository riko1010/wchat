# Readme Guide
0.0.3-0.0.8-dev

#Mods
processlines $Date wrsp in try/catch block

## Install
config in assets-php/settings.php
conversations folder holds chat folders containing chat files and media
dropbox can host conversations folder which is downloaded to local conversations folder on content change. You should create or configure a dropbox app with dropbox.php as the webhook url for changes in your Dropbox account. when the webhook is sent, a check for folder size change will occur at the provided dropbox folder zip download link {$dropboxfolderuriaszip}, if change in last fetched size stored using lazerdb(flat file db) a download will occur replacing conversations folder(assumption is dropbox folder is mirrored to local conversations folder).
placing chat folders in conversations would work fine, extensionless files from whatsapp exports will be fixed.
most services will be removed for local rendering, if you wish change the services to preferred alternatives. SQLite will replace all db in next dev release.


php and python
works without python, php zip renames files ending with '.' to _, python unzips fine so unzip of dropbox conversations folder fetched on webhook notice happens with python. Will consider a php unzip to reduce dependence on python.
Replicate features btw python and PHP.


python config in python/archive.py , python/unzip.py - will centralize config btw php and python
- python handles unzip
- python handles fixing extensionless files, identifying file extension, renaming with proper extension and fixing references to files in chatfiles.
- python handles archiving with pypi/waybackpy
- python handles screnshot of video loaded using a webvideo player then screenshot using a browserscreenshot service.

## FEATURE LIST
- search, will be easier when chatfiles are exported to db
-  \+,_ removal in international numbers on any url path
-  multiple chat files in one folder ( i.e file.1.txt, file.2.txt )
-  attachment identifiers and renderers (images,doc,media)
-  sitemap generation (xml) on conversations folder modification through dropbox webhook
-  archive through archive.org spn (spn@archive.org) mail(returns links via email)
-  extensionless file fix (filename.) through unzip.py on webhook notice of folder change from dropbox, and, php, on requesting chatfile
-  pyscripts in python , .htaccess enables cgiexec in python
-  search
-  sqlite for db, db & tables are created on init, updated on directory modification
  
## TODO
-  unreachable python urls will fail silently
- url, archive.org url, permanent shortlink
-  generate sitemap.xml, through unzip.py
-  error logger - whoops to slack, mail, other, loggly, telegram,
-  python singleton ? maxtime exec independent of singleton
-  external services removal for local rendering, google docs viewer, others
-  centralize config btw php and python
-  database choice, mysql or sqlite

#Restructuring
the current architecture syncs from dropbox public folders, which is convenient but every sync deletes the existing chatfiles. the plan is to store the conversations in database which would help easier search and other planned features.
ideally the chatfiles can be uploaded manually and saved to db with an option to overwrite.
the new architecture has benefits for public deployment.