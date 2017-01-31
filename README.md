These are some simple instructions on how to setup and use these files for a Magnum & Kubernetes Demo.

Most of the work has already been setup and populated already, but here are the steps from a git clone.

* git clone https://github.com/dirkmueller/cloud-on-arm-demo to Demo Host machine

* copy directory cloud-on-arm-demo/dashboard/k8s/* to controller at 192.168.124.81
```
# scp -r /cloud-on-arm-demo/dashboard/k8s/* root@192.168.124.81:/srv/www/openstack-dashboard/media/k8s/
```
* Edit the index.html file and change line 12 to the following. This is the style sheet for SOC7.
```
<link rel="stylesheet" href="/static/dashboard/css/e4f35630b8c5.css" type="text/css" />
```
delete lines 626 and 628 since you won’t be needing these logos for this demo. Save and exit the file.

* Install apache2-mod_php5 and php5 (You can find these in the web and scripting module)
```
# zypper in apache2-mod_php5-5.5.14-89.2.x86_64.rpm php5-5.5.14-89.2.x86_64.rpm 
# a2enmod php5
# rcapache2 restart
```
* Now we can access the AutoScale Dashboard http://192.168.124.81/media/k8s/ where you will see two graphs side by side for containers right and containers left.

* Copy scripts and kubernetes-templates to the kube-master node (this is the IP Magnum gave this node on the floating network)
```
# scp -r -i /location/of/cert/default.pem /kubernetes-templates/demo/* 192.168.126.131:/root/
# scp -r -i /location/of/cert/default.pem /scripts/* 192.168.126.131:/root/
```
* Copy scripts to the controller node 
```
# scp -r /scripts/* 192.168.124.81:/root/
```
* Edit the kubernetes-dashboard.yaml

>Line 38 needs to be changed to amd64 image like below:
>image: gcr.io/google_containers/kubernetes-dashboard-amd64:v1.4.0
>Line 47 needs the IP address changed to the assigned internal address of the kube-master0 like below:

`- --apiserver-host=http://10.0.0.7:8080`

* To view the kubernetes dashboard we must start it. 
```
# kubectl create -f kubernetes-dashboard.yaml
```
* Once its started we can view it with the following
```
# kubectl get pods –namespace=kube-system
```
* To access the kubernetes dashboard we need to know its port
```
# kubectl describe -f kubernetes-dashboard.yaml | grep NodePort

Type:                   NodePort 
NodePort:               <unset> 30681/TCP
```
Notice the number after unset. That number is the port you can reach kubernetes dashboard on.

http://192.168.126.138(Public IP of kube-minion its running on):30681

* Edit nginx-left.yaml and nginx-right.yaml
```
Line 14 change to 10 replicas
Change line 26 to nginx like below:
- image: nginx
```
* Now we can create these containers with the following:
```
# kubectl create -f nginx-left.yaml
# kubectl create -f nginx-right.yaml
```
* Install the kubernetes-client (This can be found in the OBS built for SLE 12 SP2)
```
zypper in kubernetes-client-1.3.10-5.1.x86_64.rpm
```
* Now lets move over to the controller node and look at the readscale.sh script and edit line 15 to look like below:
```
kubectl --server=http://192.168.126.131:8080 scale --replicas $current rc/nginx-$side; sleep 1;
```
The --server option is to tell where the api server is which in this case is the kube-master node.

* Execute the readscale.sh script
```
# ./readscale.sh &
```
* On the Host linux machine you need to make sure you have installed pyalsaaudio and pyalsa

* Edit /client/volume-right.py /client/volume-left.py on the Host machine

Line 35 change base_jobs to 2

This will make sure that there are always 2 containers running on each side

* On the Host linux machine you can execute the /client/volume-right.py script and /client/volume-left.py script in another shell. This will gather noise from the mic on your laptop or whatever your host machine is and send the value to set-right.php and set-left.php which will in turn updates some .json files in the /srv/www/openstack-dashboard/media/k8s/ directory.  The readscale.sh script executed earlier then reads the json file and updates the scaling with a kubectl command. To stop the script you must use ctrl-z then kill %1 as the script doesn’t properly receive a signal with a ctrl-c and die. 

**Note:** The scaling is very sensitive and with limited resources on the machine I have limited the number of containers on the left to 20 and the right to 30 so you will only see the graph bounce up to 20 and 30 respectively. Once the graph gets up to 20 and 30 it takes a few moments to go back down because of pending tasks on the kubernetes master which  you can watch on the kubernetes dashboard. The stand still amount of containers is 2 on each side so you will see things level out at 2 containers on the graphs.
Your auto scale environment is now properly setup. 
