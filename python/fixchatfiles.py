#!/opt/hc_python/bin/python3
import os, zipfile, shutil, urllib.parse, requests, sys
import os.path, fileinput, re, logging
from pymodules.filetype import filetype
from includes.classes import ap, pj, uritoencode
from pathlib import Path

logging.basicConfig(filename='logs/fixchatfiles.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
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

#unzip functions removed. only fixes chatfiles.       

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
# fixchatfiles.py fetch screenshot of video from https://screenia.best then save as filename.ext.png
try:
  fpy = open(pj(incd, incScreenshot))
  exec(fpy.read())
except:
  logging.exception('cannot execute included file')
  sys.exit(1)
else:
  fpy.close