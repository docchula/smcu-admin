FROM ghcr.io/serversideup/php:8.5-frankenphp AS base

# Install additional php extensions (requires root)
USER root
RUN install-php-extensions gd intl

WORKDIR ${APP_BASE_DIR}
COPY composer.json composer.lock ./
# Install PHP dependencies first to allow caching PHP dependencies in case of minor update
# Skip scripts as running scripts require complete codebase
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts
COPY . .
RUN composer run post-autoload-dump

# Build Javascript assets
FROM mirror.gcr.io/node:24-alpine AS node
ENV APP_BASE_DIR=/var/www/html
ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"

RUN corepack enable && corepack prepare pnpm@11 --activate
WORKDIR /app
# Copy only files needed for JS dependencies first to leverage caching
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN --mount=type=cache,id=pnpm,target=/pnpm/store pnpm install --frozen-lockfile
COPY --from=base ${APP_BASE_DIR} /app
RUN pnpm build

FROM base
COPY --from=node /app/public/build ${APP_BASE_DIR}/public/build
RUN chown -R www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Set some static environment variables
# Exporting large spreadsheets requires more memory
ENV APP_ENV=production APP_DEBUG=false
ENV REDIS_HOST=redis REDIS_PASSWORD=null REDIS_PORT=6379
ENV CACHE_STORE=redis LOG_CHANNEL=stderr SESSION_DRIVER=redis
ENV PHP_MEMORY_LIMIT=400M PHP_OPCACHE_ENABLE=1
ENV PHP_POST_MAX_SIZE=30M PHP_UPLOAD_MAX_FILE_SIZE=30M
ENV OCTANE_SERVER=frankenphp HEALTHCHECK_PATH=/up

# Optimize
RUN php artisan octane:install --server=frankenphp --no-interaction
RUN php artisan optimize
RUN php artisan config:clear # To make environment variables work

USER www-data
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--port=8080"]
