#!/bin/bash

# deploy changes
cd sandbox.cloudloan.qlsportal.com
git pull origin develop
composer install --no-dev
php artisan migrate:rollback
php artisan migrate --seed