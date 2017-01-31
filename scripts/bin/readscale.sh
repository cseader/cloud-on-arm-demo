#!/bin/bash

current=2
lastval_left=0
lastval_right=0

while true; do 

  for side in left right; do
    current=$(tail -n 2 /srv/www/openstack-dashboard/media/k8s/demo-${side}.json | head  -n 1 | cut -d: -f4)
   
    lastval_var=lastval_${side}
    if [ "$current" != "${!lastval_var}" ]; then
       echo "scaling $side to $current"
       kubectl --server=http://192.168.126.131:8080 scale --replicas $current rc/nginx-$side; sleep 1;  
       let ${lastval_var}=$current
    fi
  done

  sleep 0.4
done
