FROM serversideup/php:8.3-unit AS base

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
FROM node:22-slim AS node
ENV APP_BASE_DIR=/var/www/html
ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"
# corepack is an experimental feature in Node.js v20 which allows
# installing and managing versions of pnpm, npm, yarn
RUN npm install -g corepack@latest
RUN corepack enable && corepack prepare pnpm@latest --activate
WORKDIR /app
# Copy only files needed for JS dependencies first to leverage caching
COPY package.json pnpm-lock.yaml ./
RUN --mount=type=cache,id=pnpm,target=/pnpm/store pnpm install --frozen-lockfile
COPY --from=base ${APP_BASE_DIR} /app
RUN pnpm build

FROM base
COPY --from=node /app/public/build ${APP_BASE_DIR}/public/build
RUN chown -R www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Optimize
RUN php artisan view:cache

USER www-data
CMD ["unitd", "--no-daemon"]
