ARG php_version="8.5.5"
ARG alpine_version="3.21"
ARG composer_version="2.8.6"
ARG supervisor_version="=~4.2"
ARG node_version="=~22"
ARG nginx_version="=~1.26"

ARG APP_NAME="Palety"

# @Workaround: Template replace not working in 'COPY --from=' syntax
#  COPY --from=composer:${composer_version}
FROM composer:${composer_version} AS composer


##################################################
###   Base build, shared by all app services   ###
##################################################
FROM php:${php_version}-fpm-alpine${alpine_version} AS base

ARG supervisor_version
# PDF env vars, defined here for caching purposes
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# helper scripts for docker
COPY .docker/scripts/docker-php-ext-get /usr/local/bin

# install php extensions
RUN apk --update add \
    zlib-dev \
    libpng-dev \
    libxpm-dev \
    libwebp-dev \
    libxml2-dev \
    libjpeg-turbo-dev \
    libpq-dev \
    freetype-dev \
    icu-dev \
    linux-headers \
 && docker-php-source extract \
 && docker-php-ext-configure \
    calendar \
 && docker-php-ext-configure gd \
    --with-xpm \
    --with-jpeg \
    --with-webp \
    --with-freetype \
 && docker-php-ext-install \
    sockets \
    calendar \
    pcntl \
    bcmath \
    opcache \
    pdo \
    pdo_pgsql \
    gd \
 && docker-php-ext-configure \
    intl \
 && docker-php-ext-install \
    intl \
 && docker-php-source delete \
 && rm -rf /tmp/* /var/cache/apk/*

# install editors and supervisor
RUN apk --update add \
    less \
    nano \
    "supervisor${supervisor_version}" \
 && rm -rf /tmp/* /var/cache/apk/*

# grab composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# install node and npm
RUN apk --update add \
    "nodejs${node_version}" npm \
 && npm install -g corepack \
 && rm -rf /tmp/* /var/cache/apk/*

# PDF generation
RUN apk --update add \
    chromium \
    ghostscript \
 && rm -rf /tmp/* /var/cache/apk/*


#############################################
###   Builder step, set up common stuff   ###
#############################################
FROM base AS builder

ARG node_version
ARG NODE_ENV='production'
ARG APP_NAME
ARG VITE_APP_NAME="${APP_NAME}"

WORKDIR /var/www/html

# composer dependencies
COPY composer.json \
     composer.lock \
     ./

# only install packages for now – for docker caching purposes, run scripts
# and autoload once all the sourcecode is copied over
RUN --mount=type=secret,id=FLUX_AUTH_JSON_FILE,target=auth.json \
    composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader

# node dependencies – more likely to change than composer
COPY package.json \
     yarn.lock \
     vite.config.js \
     ./

# we only need the the packages for building; not in production,
# so install install them in a tmpfs layer to avoid image bloat
RUN corepack enable \
 && yarn install \
    --modules-folder /tmp/yarn/node_modules \
    --frozen-lockfile \
    --production=false \
    --non-interactive \
    --ignore-scripts

# copy all the sourcecode – relying on .dockerignore to not get unwanted files
COPY artisan ./
COPY app         app
COPY bootstrap   bootstrap
COPY config      config
COPY database    database
COPY public      public
COPY resources   resources
COPY routes      routes

RUN mkdir -p bootstrap/cache \
 && mkdir -p storage/app/public \
 && mkdir -p storage/framework/cache/data \
 && mkdir -p storage/framework/sessions \
 && mkdir -p storage/framework/testing \
 && mkdir -p storage/framework/views \
 && mkdir -p storage/logs

# create autoload files for composer
RUN composer dump-autoload --optimize --no-dev \
 && composer run-script --no-dev post-update-cmd

RUN ln -s /tmp/yarn/node_modules node_modules \
    && yarn run build \
    && rm node_modules

# PDF generation
# Temporarily swap package.json for an empty one
# to only install puppeteer
RUN mv package.json package.json.bak || true \
    && echo '{}' > package.json \
    && npm install puppeteer --no-save --no-fund \
    && rm package.json \
    && mv package.json.bak package.json


#################################################
###   Steps for building the main app image   ###
#################################################
FROM base AS app
ARG nginx_version

ENV APP_LOCALE=en
ENV APP_FALLBACK_LOCALE=en
ENV BCRYPT_ROUNDS=12
ARG APP_NAME
ENV APP_NAME="${APP_NAME}"

ENV LOG_STACK=stderr

EXPOSE 80

RUN apk --update add \
    "nginx${nginx_version}" \
 && rm -rf /var/cache/apk/*

 # create directory required by nginx
RUN mkdir -p /run/nginx \
 && chown nginx:nginx /run/nginx

WORKDIR /var/www/html

# copy builder files and configurations
COPY --from=builder --chown=www-data:www-data /var/www/html ./
COPY .docker/config/supervisor/cloud.conf /etc/supervisord.conf
COPY .docker/config/nginx/default.conf /etc/nginx/http.d/default.conf
COPY .docker/config/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# export vendor resources
RUN php artisan vendor:publish -n --provider='Pine\I18n\I18nServiceProvider'

# set correct file ownership
RUN chown -R www-data:www-data .

# create directory required by supervisor
RUN mkdir -p /var/log/supervisor

# configure cron
RUN echo '* * * * * php /var/www/html/artisan schedule:run' >> /etc/crontabs/root

COPY .docker/scripts/entrypoint.sh /entrypoint.sh
RUN chmod 744 /entrypoint.sh

ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]

CMD ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
