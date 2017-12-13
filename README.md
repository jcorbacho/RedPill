##Content
.
├── etc
│   ├── traefik.toml
│   └── traefik.toml.original
├── jenkins-docker
│   └── Dockerfile
├── Jenkinsfile --> DevOpsSec World
├── LICENSE
├── Makefile --> Ops World
├── README.md
├── sonar-docker
│   └── Dockerfile
└── src
    ├── php
    │   ├── debian.png
    │   ├── Dockerfile --> Dev World
    │   ├── docker.png
    │   ├── index.php
    │   ├── jenkins.png
    │   ├── lamp.jpg
    │   ├── owasp.png
    │   ├── scaleway.svg
    │   ├── star.png
    │   └── traefik.png
    └── sql
        ├── production.sql
        └── staging.sql

Demo video: https://youtu.be/aaeiCdTwDVo?list=PLUOjNfYgonUvu4EZ4m6OxVovCd18M6jTv


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

	sudo chmod 600 /tmp/acme.json

traefik directory for logs
	mkdir /tmp/traefik

Exposed ports:
- 8666 --> Traefik dashboard interface (internally 8080)
- 80 --> PHP app
- 443 --> PHP app

Sharing docker.sock file into the container so that the Traefik process can listen for changes to containers

	docker run -d -p 8666:8080 -p 80:80 -p 443:443 --name traefik \
	-v /var/run/docker.sock:/var/run/docker.sock \  
        -v /tmp/traefik:/data -v $PWD/etc/traefik.toml:/traefik.toml -v /tmp/acme.json:/acme.json traefik  

Adding domains used in traefik.toml

	echo "127.0.0.1   staging.localhost" >> /etc/hosts
	echo "127.0.0.1   bb.localhost" >> /etc/hosts
	echo "127.0.0.1   prod.localhost" >> /etc/hosts


Jenkins: 
Providing docker binary and docker socket for being able to execute docker inside the Jenkins container

	mkdir /tmp/jenkins_home

	docker build -t jenkins-ejoscor:latest jenkins-docker/.

	docker network create jenkins-sonar-net

	docker run --rm -d -p 8080:8080 -p 50000:50000 --net jenkins-sonar-net --name jenkins-master\
	-v /tmp/jenkins_home:/var/jenkins_home \
	-v /var/run/docker.sock:/var/run/docker.sock \
	-v $(which docker):/usr/bin/docker \
        -v $(which make):/usr/bin/make \
	--label traefik.backend='jenkins' \
	--label traefik.port='8080' \
	--label traefik.protocol='http' \  
        --label traefik.weight='10'\
	--label traefik.frontend.rule='Host:localhost' \  
        --label traefik.frontend.passHostHeader='true' \
	--label traefik.priority='10' \
	jenkins-ejoscor:latest

Configure Jenkins server:
- Admin crendentials: sudo cat /tmp/jenkins_home/secrets/initialAdminPassword
- Manage Jenkins --> Manage Plugins --> Available --> Install plugins "SonarQube Scanner for Jenkins", 
		"Docker Pipeline", "Pipeline: Multibranch with defaults", "GitHub plugin", "Pipeline: Declarative", 
		"Pipeline: Stage View Plugin", "Pipeline: Input Step"
- Manage Jenkins --> Global Tools Configuration --> SonarQube Scanner -->  Name: "SonarQube Scanner"
- Manage Jenkins --> Configure System --> Add SonarQube server --> Name "SonarQube", Server URL: "http://sonar-server:9000"
- New item --> Name:"my-pipeline", Type:"Pipeline", Pipeline definition --> Definition:"Pipeline script from SCM", SCM:"git", Repository URL: "https://github.com/jcorbacho/RedPill.git", Script Path:"Jenkinsfile"
- Clarifications about sonarqube section inside Jenkinsfile:
	The tool name "SonarQube Scanner" needs to match the "Name" field of a SonarQube Installation on the Global Tools Configuration page. 
	The name used in the withSonarQubeEnv step needs to match the "Name" field of a SonarQube server defined on the Configure System page.

Sonar:

	docker build -t sonar-ejoscor:latest sonar-docker/.
	docker run --rm -d -p 9000:9000 --net jenkins-sonar-net --name sonar-server sonar-ejoscor:latest


sqli :  
http://prod.localhost/?id=1%20UNION%20SELECT%20username,%20nom,%20prenom,%20email%20FROM%20users;  

correct:  
$user_id = intval($_GET['id']);  


