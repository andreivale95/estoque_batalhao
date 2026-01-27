release: php artisan migrate --force && php artisan config:cache && php artisan route:cache
# Web process is handled by Dockerfile CMD directive for Docker deployments
# For Heroku (non-Docker), use: web: vendor/bin/heroku-php-apache2 public/