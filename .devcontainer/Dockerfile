FROM serversideup/php:8.3-unit AS base

# Install additional php extensions (requires root)
USER root
RUN install-php-extensions gd intl redis

# Install Node.js
RUN apt-get update && apt-get install -y nodejs npm
RUN npm install -g pnpm

# Copy the project files
COPY . ${APP_BASE_DIR}
WORKDIR ${APP_BASE_DIR}
RUN mkdir bootstrap/cache
RUN chown -R www-data /var/www/html/

USER www-data

# Install PHP & JS dependencies
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader
RUN pnpm install --frozen-lockfile
RUN pnpm build

RUN touch database/database.sqlite
RUN cp -n .env.example .env
RUN php artisan key:generate
RUN php artisan migrate
CMD ["unitd", "--no-daemon"]
