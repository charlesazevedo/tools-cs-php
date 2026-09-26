# Imagem com PHPMD, PHP_CodeSniffer, PHPStan e Psalm nas versões fixadas na L4
# (ver ../VERSOES.md). As ferramentas são os PHARs oficiais dos releases do
# GitHub, conferidos pelo SHA-256 abaixo; o build falha se algum mudar.
FROM php:8.3.33-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.10.3 /usr/bin/composer /usr/local/bin/composer

ARG PHPMD_VERSAO=2.15.0
ARG PHPCS_VERSAO=4.0.4
ARG PHPSTAN_VERSAO=2.2.16
ARG PSALM_VERSAO=6.18.0

ADD https://github.com/phpmd/phpmd/releases/download/${PHPMD_VERSAO}/phpmd.phar /opt/ferramentas/phpmd.phar
ADD https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/download/${PHPCS_VERSAO}/phpcs.phar /opt/ferramentas/phpcs.phar
ADD https://github.com/phpstan/phpstan/releases/download/${PHPSTAN_VERSAO}/phpstan.phar /opt/ferramentas/phpstan.phar
ADD https://github.com/vimeo/psalm/releases/download/${PSALM_VERSAO}/psalm.phar /opt/ferramentas/psalm.phar

COPY sha256sums.txt /opt/ferramentas/sha256sums.txt
RUN cd /opt/ferramentas && sha256sum -c sha256sums.txt && chmod 0755 *.phar \
    && printf 'memory_limit=4G\n' > /usr/local/etc/php/conf.d/experimento.ini

WORKDIR /trabalho
CMD ["sleep", "infinity"]
