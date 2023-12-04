# Makefile

EXCLUDES_FILE=rsync-exclude
CONTAINER_NAME=fishing-store
CONTAINER_TARGET_PATH=/opt/drupal
SERVER_UPLOAD_TARGET_PATH=serwer2328033@serwer2328033.home.pl:/home/serwer2328033/public_html/fishing-store/

docker-build:
	docker-compose build

docker-up:
	docker-compose up -d

docker-deploy: docker-build docker-up

docker-undeploy:
	docker-compose down

docker-upload-dry-run:
	rsync -av --dry-run -e 'docker exec -i' --delete --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)

docker-upload:
	rsync -av -e 'docker exec -i' --delete --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)

upload-dry-run:
	rsync -av --dry-run -e "ssh -p 22222" --delete --exclude-from=$(EXCLUDES_FILE) ./ $(SERVER_UPLOAD_TARGET_PATH)

upload:
	rsync -av -e "ssh -p 22222" --delete --exclude-from=$(EXCLUDES_FILE) ./ $(SERVER_UPLOAD_TARGET_PATH)
