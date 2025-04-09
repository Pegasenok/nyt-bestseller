### Build 
```bash
# Docker build process has some caveats because of `Laravel Sail` integration
export APP_PORT=${APP_PORT:-80}
export APP_SERVICE=${APP_SERVICE:-"laravel.test"}
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}
docker compose build
```

### Run tests
```bash
docker compose up -d
docker compose exec -it laravel.test ./vendor/bin/phpunit
```
-----

### Miscellaneous:
```bash
# Init application steps
composer global require laravel/installer
export PATH="/home/paul/.config/composer/vendor/bin:$PATH"
laravel new nyt-bestseller
# Add sail
php artisan sail:install
subl ~/.bashrc
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```
```bash
sail up
sail composer install
sail artisan migrate
sail stop
```
```bash
# Xdebug - `sail debug test` does not properly connect, use direct command instead
docker compose exec -it laravel.test ./vendor/bin/phpunit
sail test --env=testing # without specifying env doesn't set it correctly
```
