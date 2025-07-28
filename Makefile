SHELL := /bin/bash

PROJECT = notebook
BUILD_DIR := build
CSS_DIR := css
JS_DIR := js
DEV_DIR := ../nextcloud-docker-dev/workspace/server/apps-extra
PROJECT_DEV_DIR := $(DEV_DIR)/$(PROJECT)
REMOTE := raspberrypi

TARGET := $(BUILD_DIR)/$(PROJECT).tar.gz

# Run make DEBUG=0 for production mode
DEBUG ?= 1
ifeq ($(DEBUG), 1)
	NPM_BUILD_COMMAND = dev
else
	NPM_BUILD_COMMAND = build
endif

.PHONY: clean export_development export_aio export_aio_inner super_clean

$(TARGET): clean $(BUILD_DIR) node_modules
	npm run $(NPM_BUILD_COMMAND)
	tar -cf $@ \
		appinfo \
		img \
		js \
		lib \
		package.json \
		src \
		templates

# TAR NONSENSE:
# https://docs.docker.com/reference/cli/docker/container/cp/
export_development: $(TARGET)
	sudo echo "SUDO ENABLED"
	sudo rm -rf $(PROJECT_DEV_DIR)
	sudo mkdir $(PROJECT_DEV_DIR)
	cat $(TARGET) | sudo tar Cxf $(PROJECT_DEV_DIR) -

# https://stackoverflow.com/questions/10310299/what-is-the-proper-way-to-sudo-over-ssh#10312700
# Ensures tempfolder is deleted everytime, even if inner errors
# Will not ensure deletion if process is interrupted
# Perhaps update with fancy tempdir: mydir=$(mktemp -d "${TMPDIR:-/tmp/}$(basename $0).XXXXXXXXXXXX")
# Source: https://stackoverflow.com/questions/10982911/creating-temporary-files-in-bash
export_aio: $(TARGET)
	$(MAKE) $(MFLAGS) export_aio_inner
	-rm -rf $(BUILD_DIR)/payload

# LIMITATION: AIO by default is not happy with overwriting folders with new versions
# Specifically modifying the version in appinfo/index.xml
# Perhaps running the occ app:update command
# would fix things
export_aio_inner:
	# Enter remote sudo password:
	read sudo_pass && echo $$sudo_pass > $(BUILD_DIR)/payload
	rsync -vP $(TARGET) $(REMOTE):/tmp
	cat $(BUILD_DIR)/payload | ssh $(REMOTE) sudo -S --prompt="" -- whoami \& sudo docker exec --user www-data -i nextcloud-aio-nextcloud rm -rf /var/www/html/custom_apps/$(PROJECT)
	cat $(BUILD_DIR)/payload | ssh $(REMOTE) sudo -S --prompt="" -- whoami \& sudo docker exec --user www-data -i nextcloud-aio-nextcloud mkdir /var/www/html/custom_apps/$(PROJECT)
	cat $(BUILD_DIR)/payload | ssh $(REMOTE) sudo -S --prompt="" -- whoami \& cat /tmp/$(PROJECT).tar.gz \| sudo docker exec --user www-data -i nextcloud-aio-nextcloud tar Cxf /var/www/html/custom_apps/$(PROJECT) -

$(BUILD_DIR):
	@mkdir -p $@

node_modules:
	npm install

clean:
	@rm -rf $(BUILD_DIR) $(CSS_DIR) $(JS_DIR)

super_clean: clean
	@rm -rf node_modules

