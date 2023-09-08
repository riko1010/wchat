import os.path, urllib.parse
#ansolute path
def ap(cd, filename):
  return os.path.abspath( os.path.join( cd, filename ) )
#parse directory recursively for mp4 files
def uritoencode(uritoencode):
  return urllib.parse.quote(uritoencode, safe="")
#joinpath  
def pj(cd, filename):
  return os.path.join( cd, filename )