##Content
.  
├── Dockerfile -> Dev World  
├── Jenkinsfile -> DevOpsSec World  
├── LICENSE  
├── Makefile -> Ops World  
├── README.md  
└── src  
    ├── php  
    │   └── index.php  
    └── sql  
        ├── production.sql  
        └── staging.sql  

3 directories, 8 files  


docker registry:
	docker run -d -p 5000:5000 --restart=always --name registry registry

traefik :  

Traefik tutorial: https://www.digitalocean.com/community/tutorials/how-to-use-traefik-as-a-reverse-proxy-for-docker-containers-on-ubuntu-16-04

Traefik providers:
- web (which gives you access to a dashboard interface)
- docker 
- acme (which is used to support TLS using Let's Encrypt)

acme.json used to store the information that we will receive from Let's Encrypt 
(ACME is the name of the protocol used to communicate with Let's Encrypt to manage certificates)

	touch /tmp/acme.json

Lock down the permissions on this file so that only the root user can read and write to this file. 
If you don't do this, Traefik will fail to start.

	chmod 600 /tmp/acme.json

traefik directory for logs
	mkdir /tmp/traefik

Exposed ports:
- 8666 --> Traefik dashboard interface (internally 8080)
- 80 --> PHP app
- 443 --> PHP app

Sharing docker.sock file into the container so that the Traefik process can listen for changes to containers

	docker run -d -p 8666:8080 -p 80:80 -p 443:443 -v /var/run/docker.sock:/var/run/docker.sock \  
        -v /tmp/traefik:/data -v $PWD/etc/traefik.toml:/traefik.toml -v /tmp/acme.json:/acme.json traefik  

	


Jenkins:  
	mkdir /tmp/jenkins_home
	cp Jenkinsfile /tmp/jenkins_home

	docker run -p 8080 -d \
	-v /tmp/jenkins_home:/var/jenkins_home \
	-v /var/run/docker.sock:/var/run/docker.sock \
	-v $(which docker):/$  \
        -v $(which make):/usr/bin/make \
	--label traefik.backend='jenkins' \
	--label traefik.port='8080' \
	--label traefik.protocol='http' \  
        --label traefik.weight='10'\
	--label traefik.frontend.rule='Host:chocobo.yogosha.com' \  
        --label traefik.frontend.passHostHeader='true' \
	--label traefik.priority='10' \
	jenkinsci/docker-workflow-demo  


	docker run -d -p 9080:8080 -v /var/run/docker.sock:/var/run/docker.sock -v $(which docker):/usr/bin/docker  -v $(which make):/usr/bin/make  -v /tmp/jenkins-data:/var/jenkins_home jenkins


	docker run -d -p 9080:8080 -v $(which docker):/usr/bin/docker  -v $(which make):/usr/bin/make  -v /tmp/jenkins-data:/var/jenkins_home jenkins/jenkins:lts


	docker run -d -p 9080:8080 -v /var/run/docker.sock:/var/run/docker.sock -v $(which docker):/usr/bin/docker  -v $(which make):/usr/bin/make  -v /tmp/jenkins-data:/var/jenkins_home jenkins-ejoscor:latest


sqli :  
http://chocobo.yogosha.com:32808/?id=1%20UNION%20SELECT%20username,%20nom,%20prenom,%20email%20FROM%20users;  

correct:  
$user_id = intval($_GET['id']);  


