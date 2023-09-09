##Readme Guide
#feature list
-  \+ removal in international numbers or any
-  multiple chat files in one folder (naming conflict resolution by filename.{int}.txt )
-  attachment identifiers (images,doc,media)
-  sitemap generation (xml) on conversations folder modification through dropbox webhook
-  archive through archive.org spn (pypi/waybackpy), sitemap generation trigges archive, links are from sitemap.csv(updated) and written to archivedsitemap.txt ( takes 6 mins each url when throttled ) - sitemap.csv maintains archived links to avoid duplication.
-  extensionless file fix (filename.) through unzip.py on webhook notice of folder change from dropbox, and, php, on requesting chatfile
-  pyscripts in python , .htaccess enables cgiexec in python
  
todo
-  context menu, floating menu, url, archive.org url, permanent shortlink
-  permanent shortlink to archive.org spn (may use cors requests hack)
-  generate sitemap.xml, through unzip.py
-  error logger - whoops to slack, mail, other, loggly, telegram,
-  python singleton ? maxtime exec independent of singleton
  
thought process
-  permanent shortlink to archive.org spn compared to page is to provide permanent links, some share cases would require permanent links compared to hosted copy.

removed features
-  archive through mail to archive@ and spn@ , the expectation was return of archived links which i did not receive, the waybackpy module returns links on archive making it easy to save these links and archive only when contents change.
-  sitemap.txt removed, sitemap.csv replaces sitemap.txt and may replace archivedlinks.txt