#!/opt/hc_python/bin/python3
from pymodules.waybackpy import WaybackMachineSaveAPI
import os, sys, logging, csv, datetime
from includes.classes import ap, pj

libPath = 'pymodules'
if not libPath in sys.path: 
  sys.path.append(libPath)
from pymodules import comma

logging.basicConfig(filename='logs/archive.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

# specify the base folder path
cd = "../"
cp = 'conversations'
user_agent = "Mozilla/5.0 (Windows NT 5.1; rv:70.0) Gecko/20130101 Firefox/70.0"
sitemapcsv = 'sitemap.csv'
asitemap = 'archivedsitemap.txt'
#terminate at end of iter count - debugging
limit = 0

class Archive:
  rearchive = None 
  iterable = None
  def archive(self):
    #counters, arrays
    skipped = 0
    archived = 0
    newlines = []
    counter = 1
    iterablecounter = len(self.iterable)
      
    for line in self.iterable:
      url = line['url']
      if (limit != 0):
        logging.info('limit')
        if (counter == limit):
          logging.info('break')
          break
      line['synctime'] = datetime.datetime.now().strftime("%d.%m.%y %I:%M %p")
      if line['sync'] != '1':
        logging.info(f'skipping {counter}:{url}')
        counter += 1
        skipped += 1
        continue
      logging.info(f'Archiving {counter}:{url}')
      save_api = WaybackMachineSaveAPI(url, user_agent)
      try:
        saveurl = save_api.save()
        if (saveurl == ''):
          logging.error(f'Archiving {url} failed - empty response(install waybackpy globally if persists)')
          raise Exception (f'Archiving {url} failed')
      except:
        logging.exception(f'Archiving {counter} url:{url} failed')
      else:
        logging.info('writing to file - comma seems to modify csv realtime similar to realtime db modification')
        line['archivedurl'] = saveurl
        line['sync'] = '0'
        archived = +1
        newlines.append(saveurl)
        logging.info(f'Archiving {counter} url:{url} as {saveurl} done')
      counter +=1
        
    if (len(newlines) == 0 and skipped == 0):
        logging.info('Archiving failed')
        sys.exit(0)
    self.rearchive = iterablecounter - archived - skipped
    
    logging.info(f'skipped {skipped} of {iterablecounter}')
    logging.info(f'archived {archived} of {iterablecounter}') 
    logging.info(f'retrying {self.rearchive} of {iterablecounter}')
    
try:
  sitemapcsvlist = comma.load(ap(cd, sitemapcsv))
except:
  logging.exception(f'cannot load {sitemapcsv}')
  sys.exit(1)
      
try:
  archive = Archive()
  archive.iterable = sitemapcsvlist
  archive.archive()
except:
  logging.exception('Critical error. Archiving failed - will not retry.')
else:
  #retry once for failed archives by reason of wayback
  if archive.rearchive > 0: 
    logging.error(f'{archive.rearchive} failed. retrying once.')
    archive.archive()

try:
  comma.dump(archive.iterable, filename=ap(cd, sitemapcsv), dialect="unix")
except:
  logging.info(f'cannot write to {sitemapcsv}')
  sys.exit(1)
try:
  g = open(ap(cd, asitemap), "w")
  g.writelines([f'{x} \n' for x in archive.iterable['archivedurl']])
except:
  logging.exception(f'cannot write to {asitemap}')
  sys.exit(1)
else:
  g.close
logging.info('archiving done.')  