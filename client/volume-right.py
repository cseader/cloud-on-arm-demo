#!/usr/bin/python
## This is an example of a simple sound capture script.
##
## The script opens an ALSA pcm for sound capture. Set
## various attributes of the capture, and reads in a loop,
## Then prints the volume.
##
## To test it out, run it and shout at your microphone:

import alsaaudio, time, audioop
import urllib2
import threading

# Open the device in nonblocking capture mode. The last argument could
# just as well have been zero for blocking mode. Then we could have
# left out the sleep call in the bottom of the loop
inp = alsaaudio.PCM(alsaaudio.PCM_CAPTURE,0) # alsaaudio.PCM_NONBLOCK)

# Set attributes: Mono, 8000 Hz, 16 bit little endian samples
inp.setchannels(1)
inp.setrate(8000)
inp.setformat(alsaaudio.PCM_FORMAT_S16_LE)

# The period size controls the internal number of frames per period.
# The significance of this parameter is documented in the ALSA api.
# For our purposes, it is suficcient to know that reads from the device
# will return this many frames. Each frame being 2 bytes long.
# This means that the reads below will return either 320 bytes of data
# or 0 bytes of data. The latter is possible because we are in nonblocking
# mode.
inp.setperiodsize(160)

i = 0
vol = 0
base_jobs = 2
jobs = 0

while True:
   # Read data from device
   try:
       l,data = inp.read()
       if l:
           # Return the maximum of the absolute value of all samples in a fragment.
           if audioop.max(data, 2) > 10000:
               vol = vol + 1
   except:
       continue

   i = i + 1
   if i == 10:
       print vol
       if vol > 1:
           jobs += vol
       else:
           jobs -= 1

       if jobs < base_jobs:
           jobs = base_jobs

       #urllib2.urlopen("http://localhost/media/set.php?set=%d" % jobs).read()

       o = urllib2.build_opener()
       t = threading.Thread(target=o.open, args=("http://192.168.124.81/media/k8s/set-right.php?set=%d" % jobs,))
       t.start()

       i = 0
       vol = 0

   #time.sleep(.001)

