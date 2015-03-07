# Attempts to compile assets for fitbit-graphs
SHELL = /bin/sh
PHP = php

.PHONY: build
build: build/js/*.js build/css/*.css

build/js/%.js: src/coffee/%.coffee
	coffee --compile --lint --output build/js src/coffee/*.coffee

build/css/%.css: src/sass/%.scss
	sass --update src/sass:build/css

.PHONY: watch
watch:
	watchman watch $(shell pwd)
	watchman -- trigger $(shell pwd) remake '**/*.coffee' '**/*.scss' -- make build

.PHONY: unwatch
unwatch:
	watchman shutdown-server

.PHONY: help
help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   build (default)  Compile all assets
	#   watch            start watching files for changes and autobuidling
	#   unwatch          stop watching files for changes

