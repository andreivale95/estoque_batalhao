release: php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan storage:link || true
# Web process is handled by Dockerfile CMD directive for Docker deployments
# For Heroku (non-Docker), use: web: vendor/bin/heroku-php-apache2 public/