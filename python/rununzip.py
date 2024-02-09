#!/opt/hc_python/bin/python3
import os, subprocess, sys, logging

logging.basicConfig(filename='logs/rununzip.log', filemode='w', level=logging.DEBUG, format='%(asctime)s.%(msecs)03d %(levelname)s %(module)s - %(funcName)s: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')

try:
#  execnohup = os.system("py3=$(whereis python3 | awk '{print($2)}') && eval \"nohup $py3 unzip.py >/dev/null 2>&1 &\"")

  execnohup = subprocess.run("py3=$(whereis python3 | awk '{print($2)}') && eval \"nohup $py3 unzip.py >/dev/null 2>&1 &\"", shell="true")
except:
  logging.error('cannot exec unzip.py')
else:
  logging.info('done')

print ("Content-type:text/html\r\n\r\n")
print ("OK")