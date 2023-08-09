#!/usr/bin/env bash
php bin/console doctrine:database:drop -f
php bin/console doctrine:database:create
php bin/console doctrine:schema:update -f
sudo rabbitmqctl purge_queue newscoop_import
sudo rm -rf ninjs/insajder.net/*
sudo rm -rf ninjs/articles/*
