#!/opt/hc_python/bin/python3
logging.basicConfig(filename='logs/screenshot.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

"""
included by unzip.py
"""
  
for cd, _, files in os.walk( cp ):
    # Skip subdirs since we're only interested in files.
    for filename in files:
        if filename.endswith( '.mp4' ):
            relative_path = os.path.join( cd, filename )
            # capture the video photo
            baseurlss = urlpath+relative_path.replace('../','');
            urlss = urlscreenshot.format(uritoencode(urlvideoplayer.format((baseurlss))))
            targetpath = relative_path+posterext
            try:
              logging.info('downloading poster for %s' % relative_path)
              r = requests.get(urlss, stream=True)
              if r.status_code == 200:
                with open(targetpath, 'wb') as f:
                    r.raw.decode_content = True
                    shutil.copyfileobj(r.raw, f) 
                    f.close
            except:
              logging.error('cannot download poster for %s' % relative_path)
            else:
              logging.info('done')