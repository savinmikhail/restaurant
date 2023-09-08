.PHONY: start
start:
	sudo docker-compose build && sudo docker-compose up -d

.PHONY: bash
bash:
	sudo docker-compose exec kintsugi bash

.PHONY: restart
restart:
	sudo docker-compose down && sudo docker-compose build && sudo docker-compose up -d --remove-orphans
