#!/opt/hc_python/bin/python3
import os, zipfile, shutil, urllib.parse, requests, sys
import os.path, fileinput, re, logging
from pymodules.filetype import filetype
from includes.classes import ap, pj, uritoencode
from pathlib import Path

logging.basicConfig(filename='logs/unzip.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
# specify the zip file path 
wd = '../'
incd = 'includes'
file_path = "new-conversations.zip"
wastepath = "autodelete"
conversations_path = "conversations"
urlpath = "https://wchat.space/"
triggersitemappath = 'generate_sitemap'
chatfileattachmemtpattern = '(?:\:.*?\:)(?P<f>.*?\.)(\s\(file attached\))'
chatfileconversationpattern = '[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},'
urlscreenshot = "https://screenia.best/api/screenshot?url={0}&type=png"
urlvideoplayer = "https://onelineplayer.com/player.html?autoplay=false&autopause=false&muted=false&loop=false&url={0}&poster=&time=true&progressBar=true&overlay=true&muteButton=true&fullscreenButton=true&style=light&quality=auto&playButton=True"
posterext = '.png'

#includes
incChatfiles = 'chatfiles.py'
incScreenshot = 'screenshot.py'
  
#initialization
fp = ap(wd, file_path)
wp = ap(wd, wastepath)
cp = ap(wd, conversations_path)
tsp = ap(wd, triggersitemappath)
cfap = chatfileattachmemtpattern
cfcp = chatfileconversationpattern

# create a ZipFile object
try:
  #check if zip file exists
  if not os.path.exists(fp):
    raise Exception(f'{fp} does not exist')
  zfp = zipfile.ZipFile(fp, 'r')
# extract all the contents
  zfp.extractall(cp)
except:
  logging.exception('zip extraction error')
  sys.exit(1)
else:
  zfp.close()
  logging.info('zip extraction done')
       
# create trigger or sitemap generation trigger file
try:
  ftsp = open(tsp, "w")
except:
  logging.exception('sitemap trigger file creation error')
else:
  ftsp.close
  logging.info('sitemap trigger file created')
  
#delete file 
#check if exists
try:
  if not os.path.exists(fp):
    raise Exception(f'unzipped file {fp} does not exist')
  os.remove(fp)
#deletefolder
  shutil.rmtree(wp)
except:
  logging.exception('error deleting files')
else:
  logging.info('files & waste folder deleted')
  
#parse directory recursively for txt files. fix whatsapp chat files with extensionless attachments, rename extensionless attachments by guessing mime with filetype
try:
  gpy = open(pj(incd, incChatfiles))
  exec(gpy.read())
except:
  logging.exception('cannot execute included file or writing failed')
  sys.exit(1)
else:
  gpy.close

#parse directory recursively for mp4 files
# unzip.py fetch screenshot of video from https://screenia.best then save as filename.ext.png
try:
  fpy = open(pj(incd, incScreenshot))
  exec(fpy.read())
except:
  logging.exception('cannot execute included file')
  sys.exit(1)
else:
  fpy.close