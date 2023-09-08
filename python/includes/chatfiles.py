#!/opt/hc_python/bin/python3
logging.basicConfig(filename='logs/chatfiles.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

"""
included by unzip.py
"""
#parse directory recursively for txt files

for cd, _, files in os.walk( cp ):
    # Skip subdirs since we're only interested in files.
  for filename in files:
    if filename.endswith( '.txt' ):
      abs_path = ap(cd, filename)
      logging.info('modifying txt file:'+abs_path)
      f = open(abs_path, "r")
      newlines=[]
      for fline in f.readlines():
        atmts = re.search(cfap, fline)
        try:
          if (atmts.group('f') == ''):
            raise Exception('no file attached in line')
          aname = atmts.group('f').strip()
          atmt = ap(cd, aname)
          if not (os.path.isfile(atmt) == True):
            raise Exception ('attached file not found')
          logging.info('extensionless file exists:'+atmt)
          kind = filetype.guess(atmt)
          if (kind is None):
            raise Exception ('kind is None')
          logging.info('File extension guessed: %s' % kind.extension)
          anname = aname+kind.extension
          logging.info('Renaming from:{ap(cd, aname)}')
          logging.info('Renaming to:{ap(cd, aanname}')
          os.rename(ap(cd, aname), ap(cd, anname))
          logging.info('Renaming done')
          newlines.append(fline.replace(aname, anname))
        except:
          newlines.append(fline)
      f.close
      g = open(abs_path, "w")
      g.writelines([f'{x}\n' for x in newlines])
      logging.info('Writing done')
      g.close