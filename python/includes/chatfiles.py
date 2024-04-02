#!/opt/hc_python/bin/python3
"""
logging.basicConfig(filename='logs/chatfiles.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
"""
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
      cf = open(abs_path, "r")
      newlines=[]
      lbuffer = None
      for fline in cf.readlines():
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
          logging.info('Renaming from: %s' % ap(cd, aname))
          logging.info('Renaming to: %s' % ap(cd, anname))
          os.rename(ap(cd, aname), ap(cd, anname))
          logging.info('Renaming done')
          logging.info('%s' % fline)
          fline = fline.replace(aname, anname)
          logging.info('%s' % fline)
        except:
          logging.exception('exception:')
        fline = fline.rstrip()
        if re.search(cfcp, fline) != None:
          logging.info('conversation line found')
          if lbuffer != None:
            newlines.append(lbuffer)
            logging.info('line appended')
          lbuffer = fline
        else:
          if lbuffer == None:
            lbuffer = fline
            logging.info('line held')
          else:
            lbuffer = f'{lbuffer}<br/>{fline}'
            logging.info('line joined and held')
      if lbuffer != None:
        newlines.append(lbuffer)  
      cf.close
      try:
        g = open(abs_path, "w")
        newlines = [f'{x}\n' for x in newlines]
        g.writelines(newlines)
        g.close
        logging.info('Writing done')
      except:
        logging.exception('Writing failed')
        sys.exit(1)