#!/bin/bash

current=15
while true; do 

  if [ $(($RANDOM%100)) -ge 30 ]; then
    add=$((RANDOM%3))
  else
    add=-$(($RANDOM%3))
  fi
  current=$(($current+$add))
  if [ $current -gt 18 ]; then
    current=180
  fi
  if [ $current -lt 10 ]; then
    current=10
  fi
 
  kubectl --server=http://192.168.126.131:8080 scale --replicas $current rc/nginx-right; sleep 4;  

done
