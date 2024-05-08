FROM serversideup/php:8.3-unit AS base

# Install additional php extensions (requires root)
USER root
RUN install-php-extensions gd intl redis

# Copy the project files
COPY . ${APP_BASE_DIR}
WORKDIR ${APP_BASE_DIR}
RUN mkdir bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# Build Javascript assets
FROM node:20-slim as node
ENV APP_BASE_DIR=/var/www/html
ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"
# corepack is an experimental feature in Node.js v20 which allows
# installing and managing versions of pnpm, npm, yarn
RUN corepack enable
COPY --from=base ${APP_BASE_DIR} /app
WORKDIR /app
RUN --mount=type=cache,id=pnpm,target=/pnpm/store pnpm install --frozen-lockfile
RUN pnpm build

FROM base
COPY --from=node /app/public/build ${APP_BASE_DIR}/public/build
RUN chown -R www-data /var/www/html/storage

USER www-data
CMD ["unitd", "--no-daemon"]
