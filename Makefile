.PHONY: main build-image build-container start test shell stop clean

PROJECT_NAME := twitch-analytics
CONTAINER_NAME := $(PROJECT_NAME)-container
WORKDIR := /200/TwitchAnalytics

main: build-image build-container

build-image:
	docker build -t $(PROJECT_NAME) .

build-container:
	docker run -dt --name $(CONTAINER_NAME) -v $$(pwd):$(WORKDIR) -w $(WORKDIR) $(PROJECT_NAME)
	docker exec $(CONTAINER_NAME) composer install

start:
	docker start $(CONTAINER_NAME)

test: start
	docker exec $(CONTAINER_NAME) ./vendor/bin/phpunit tests/$(target)

shell: start
	docker exec -it $(CONTAINER_NAME) /bin/bash

stop:
	docker stop $(CONTAINER_NAME)

clean: stop
	docker rm $(CONTAINER_NAME)
	rm -rf vendor
