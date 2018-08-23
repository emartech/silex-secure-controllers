NAME 						:= silex-secure-controllers
docker-build		:= docker build --force-rm --no-cache --pull --tag
docker-run			:= docker run --rm --volume $(PWD):/var/www/html/ $(NAME) bash -c

.PHONY: help

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/\(.*\):.*##[ \t]*/    \1 ## /' | column -t -s '##'
	@echo

## Commands

destroy: ## Destroy containers and images
	-$(call destroy_containers,$(NAME))
	-$(call destroy_images,$(NAME))

build: ## Build image
	$(docker-build) $(NAME) ./

tests: ## Run tests
	$(docker-run) "composer test"

install: ## Install dependencies
	$(docker-run) "composer install 2>&1"

define destroy_containers
	docker container rm --force `docker container ls --all --quiet --filter name=$(1)` 2>/dev/null
endef

define destroy_images
	docker image rm --force `docker image ls --all --quiet $(1)` 2>/dev/null
endef
