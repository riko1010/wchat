#!/opt/hc_python/bin/python3
from pymodules.waybackpy import WaybackMachineSaveAPI
import os, sys, logging, datetime, sqlite3
from includes.classes import ap, pj

logging.basicConfig(filename='logs/archive.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

# specify the base folder path
cd = "../"
cp = 'conversations'
user_agent = "NNAI Ltd Nigeria (Namecheap Shared/VPS) Python"
#terminate at end of iter count - debugging
limit = 0
dbd = '/home/badlnykl/assets-intranet'
dbname = 'db.sqlite'
tablename = 'chatfiles'

class Archive:
  rearchive = None 
  iterable = None
  db = None
  tablename = None
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
      if line['sync'] != 1:
        logging.info(f'skipping {counter}: {url} sync:{line["sync"]}')
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
        synctime = datetime.datetime.now().strftime("%d.%m.%y %I:%M %p")
        archivedurl = saveurl
        sync = 0
        archived = +1
        newlines.append(saveurl)
        if self.db is not None:
          logging.info('updating db')
          updatedb = self.db.cursor().execute('UPDATE chatfiles SET synctime = ?, archivedurl = ?, sync = ? WHERE id = ?', (synctime, archivedurl, sync, line['id']))
          self.db.commit()
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
  connection = sqlite3.connect(ap(dbd, dbname))
  connection.row_factory = sqlite3.Row
  cursor = connection.cursor()
  sitemapsqlitelist = cursor.execute("SELECT * FROM chatfiles").fetchall()
except:
  logging.exception(f'cannot load {dbname}')
  sys.exit(1)
      
try:
  archive = Archive()
  archive.iterable = sitemapsqlitelist
  archive.db = connection
  archive.archive()
except:
  logging.exception('Critical error. Archiving failed - will not retry.')
else:
  #retry once for failed archives by reason of wayback
  if archive.rearchive > 0: 
    logging.error(f'{archive.rearchive} failed. retrying once.')
    archive.archive()

logging.info('archiving done.')  