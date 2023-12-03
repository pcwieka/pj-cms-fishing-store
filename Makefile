# Makefile

CONTAINER_NAME=fishing-store
CONTAINER_TARGET_PATH=/opt/drupal
SERVER_UPLOAD_DIRECTORY=serwer2328033@serwer2328033.home.pl:/home/serwer2328033/public_html/fishing-store/
EXCLUDES_FILE=rsync-exclude

docker-upload-dry-run:
	rsync -av --dry-run -e 'docker exec -i' --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)

docker-upload:
	rsync -av -e 'docker exec -i' --exclude-from=$(EXCLUDES_FILE) ./ $(CONTAINER_NAME):$(CONTAINER_TARGET_PATH)

upload-dry-run:
	rsync -av --dry-run -e "ssh -p 22222" --exclude-from=$(EXCLUDES_FILE) ./ $(SERVER_UPLOAD_DIRECTORY)

upload:
	rsync -av -e "ssh -p 22222" --exclude-from=$(EXCLUDES_FILE) ./ $(SERVER_UPLOAD_DIRECTORY)
