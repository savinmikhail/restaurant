CONTAINER_NAME = apache

.PHONY: start
start:
	cd ./.docker/stage && sudo docker-compose build && sudo docker-compose up -d

.PHONY: bash
bash:
	cd ./.docker/stage && sudo docker-compose exec $(CONTAINER_NAME) bash

.PHONY: restart
restart:
	cd ./.docker/stage && sudo docker-compose down && sudo docker-compose build && sudo docker-compose up -d --remove-orphans

.PHONY: test
test:
	./vendor/bin/codecept run

.PHONY: validate
validate:
	php ./vendor/bin/grumphp run