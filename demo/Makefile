# Records the demo of the README and the social preview in Docker: no local
# PHP and no VHS needed. `make -C demo all` from the root of the package.

.DEFAULT_GOAL := help

ROOT  := $(abspath $(CURDIR)/..)
IMAGE := phpunit-pest-html-assertions-demo
# As the user of the host: files the container writes stay the user's on Linux too.
RUN   := docker run --rm --user $(shell id -u):$(shell id -g) -e HOME=/tmp -v "$(ROOT):/app" -w /app $(IMAGE)

.PHONY: help build install preflight gif png all clean

help: ## Show the targets
	@grep -E '^[a-z]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "}; {printf "  %-10s %s\n", $$1, $$2}'

build: ## Build the image: VHS with PHP and Composer
	docker build -q -t $(IMAGE) $(CURDIR)

install: build ## Install the demo app, with the package from this checkout
	$(RUN) sh -c 'cd demo/app && rm -rf vendor composer.lock && composer install --no-interaction --no-progress -q'

preflight: install ## Check that every scene shows what the recording expects
	$(RUN) sh -c 'cd demo/app && vendor/bin/pest tests/StringTest.php'
	$(RUN) sh -c 'cd demo/app && vendor/bin/phpstan analyse --no-progress --error-format=json | grep -q "html.markupAsString"'
	$(RUN) sh -c 'cd demo/app && vendor/bin/pest --colors=never tests/CartTest.php | grep -q "at tests/CartTest.php"'
	$(RUN) sh -c 'cd demo/app && ! vendor/bin/pest --colors=never tests/CartTest.php | grep -q "src/PHPUnit/"'

gif: preflight ## Record output/demo.gif
	mkdir -p output
	$(RUN) vhs demo/demo.tape

png: preflight ## Record output/social.png
	mkdir -p output
	$(RUN) vhs demo/social.tape

all: gif png ## Record both

clean: ## Remove the installed demo app
	rm -rf app/vendor app/composer.lock app/.phpstan
