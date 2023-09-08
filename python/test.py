#!/opt/hc_python/bin/python3
import csv

fsitemapcsv = open('../sitemap.csv', "r", 
newline='')
sitemapcsvlist = csv.reader(fsitemapcsv) 
for line in sitemapcsvlist:
  print (line['sync']) 
#print(sitemapcsvlist.fieldnames) 

print(sitemapcsvlist) 
lsitemapcsvlist = list(sitemapcsvlist)

print(lsitemapcsvlist)
