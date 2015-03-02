.PHONY: build help
SHELL = /bin/sh
PHP = php

build:
	coffee --compile --lint --output build/js/ src/coffee/*.coffee
	sass --update src/sass:build/css

help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	#   build (default)  Compile all assets

