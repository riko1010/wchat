#Readme Guide
*feature list
-  + removal in international numbers or any
-  multiple chat files in one folder (naming conflict resolution by filename.{int}.txt )
-  attachment identifiers (images,doc,media)
-  sitemap generation (xml) on conversations folder modification
-  archive.org spn api, sitemap generation trigges archive, links are in archivedsitemap.txt ( -writing when archiving,takes 6 mins each url when throttled by archive.org )
-  archive.org spn mail through archive@ & spn@ arcive.org , sitemap generation triggers mail archive
-  extensionless file fix (filename.) through unzip.py, and, php, on requesting chatfile
  
todo
-  permanent shortlink to archive.org spn (may use cors requests hack)
-  generate sitemap.xml, sitemap.txt through unzip.py
-  archive sitemap.txt through unzip.py
-  sitemap modal - robots.txt, sitemap.xml, sitemap.txt, archivedlinks.txt
-  error logger - whoops to slack, mail, other, loggly, telegram,
-  py scripts in basedir, solution to py scripts being executed globally without running py app(may be executing globally atm)
  
thought process
-  permanent shortlink to archive.org spn compared to page is to provide permanent links, some share cases would require permanent links compared to hosted copy.
  