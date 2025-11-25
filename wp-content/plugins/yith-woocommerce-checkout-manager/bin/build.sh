#!/bin/bash

PLUGIN_FOLDER='yith-woocommerce-checkout-manager'
PLUGIN_NAME='YITH WooCommerce Checkout Manager'

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "${RED_BOLD}$1${COLOR_RESET}"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}"
}
success () {
	echo -e "${GREEN_BOLD}$1${COLOR_RESET}"
}
warning () {
	echo -e "${YELLOW_BOLD}$1${COLOR_RESET}"
}

clear_temp () {
    if [[ -e "/tmp/${PLUGIN_FOLDER}" ]]; then
	    rm -rf /tmp/${PLUGIN_FOLDER}
    fi

    if [[ -e "/tmp/${PLUGIN_FOLDER}.zip" ]]; then
	    rm /tmp/${PLUGIN_FOLDER}.zip
    fi
}

ask_for_version () {
    status "Add release version: "
    read version
    if [[ -z $version ]]
    then
		error "Version cannot be empty!"
		# call itself
		ask_for_version
    else
        PLUGIN_VERSION=$version
    fi
}

if [[ $1 != 'p' ]]
then
	status "======================================================================"
	status "Releasing ${PLUGIN_NAME}!"
	status "======================================================================"

	# Plugin Framework update
	status "Plugin Framework and Upgrade updating..."
	git submodule update --init --recursive && git submodule foreach --recursive git pull origin master

	# Run build.
	status "Build: uglify JS, generate POT and download translations..."
	npm run build

	status "Currently file changed:"
	git status -s

	status "Do you want to push online these changes (Y|n)?"
	read response
	if [[ ! $response =~ ^[Nn]$ ]]
	then
		ask_for_version
		# Commit and push online changes
		status "Pushing online..."
		git add -A
		git commit -m "Version ${PLUGIN_VERSION}"
		git push origin master
		git tag -a ${PLUGIN_VERSION} -m "Tag version ${PLUGIN_VERSION}"
		git push origin ${PLUGIN_VERSION}

		success "Done. You've pushed changes online!"
	else
		status "Do you want to reset repo to master (Y|n)?"
		read response
		if [[ ! $response =~ ^[Nn]$ ]]
		then
			# reset repo to master
			status "Resetting repo to master..."
			git reset --hard origin/master
		fi
	fi
fi

status "Do you want to create plugin package (Y|n)?"
read response
if [[ ! $response =~ ^[Nn]$ ]]
then
    # Generate the plugin zip file.
    status "Creating archive..."

    clear_temp;

    mkdir /tmp/${PLUGIN_FOLDER}

    # Copy files to temp
    rsync -a --exclude-from '.distignore' . /tmp/${PLUGIN_FOLDER}

    ( cd /tmp && zip -r -q ./${PLUGIN_FOLDER}.zip ./${PLUGIN_FOLDER} )
    cp /tmp/${PLUGIN_FOLDER}.zip ../${PLUGIN_FOLDER}.zip

    clear_temp;

    success "Done. You've created archive ${PLUGIN_FOLDER}.zip!"
fi

success "Done. Build process for plugin ${PLUGIN_NAME} completed!"
