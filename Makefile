# Makefile

PROJECT_NAME=fishing-store

EXCLUDES_FILE=rsync-exclude

CONTAINER_NAME=$(PROJECT_NAME)
CONTAINER_TARGET_PATH=/opt/drupal/fishing-store

SSH_COMMAND=ssh -p 22222
SSH_USER=serwer2328033
SSH_SERVER_URL=serwer2328033.home.pl
SSH_SERVER_TARGET_DIRECTORY=/home/$(SSH_USER)/public_html/$(PROJECT_NAME)/
SSH_SERVER_UPLOAD_FULL_PATH=$(SSH_USER)@$(SSH_SERVER_URL):$(SSH_SERVER_TARGET_DIRECTORY)
BACKUP_TIME := $(shell date '+%Y-%m-%d_%H-%M-%S')
SSH_SERVER_BACKUP_DIRECTORY=/home/$(SSH_USER)/public_html/$(PROJECT_NAME)-backup/$(PROJECT_NAME)-$(BACKUP_TIME)

compile-scss:
	cd web/themes/contrib/belgrade && npm run css:scss

# Docker
docker-build: compile-scss
	docker-compose build

docker-up:
	docker-compose up -d

docker-deploy: docker-build docker-up

docker-undeploy:
	docker-compose down

docker-upload-dry-run: compile-scss
	rsync -av --dry-run -e 'docker exec -i' --delete --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)

docker-upload: compile-scss
	rsync -av -e 'docker exec -i' --delete --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)
	docker exec $(CONTAINER_NAME) /bin/bash -c "cd $(CONTAINER_TARGET_PATH) && bin/drush cr"

# Server
ssh:
	ssh serwer2328033@serwer2328033.home.pl -p 22222

backup:
	$(SSH_COMMAND) $(SSH_USER)@$(SSH_SERVER_URL) "cp -r $(SSH_SERVER_TARGET_DIRECTORY) \"$(SSH_SERVER_BACKUP_DIRECTORY)\""

upload-dry-run: compile-scss
	rsync -av --dry-run -e "$(SSH_COMMAND)" --delete --exclude-from=$(EXCLUDES_FILE) ./ $(SSH_SERVER_UPLOAD_FULL_PATH)

upload: compile-scss
	rsync -av -e "$(SSH_COMMAND)" --delete --exclude-from=$(EXCLUDES_FILE) ./ $(SSH_SERVER_UPLOAD_FULL_PATH)
	$(SSH_COMMAND) $(SSH_USER)@$(SSH_SERVER_URL) "cd $(SSH_SERVER_TARGET_DIRECTORY) && bin/drush cr"
