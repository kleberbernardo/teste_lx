# PHP 5.6 com Apache
FROM php:5.6-apache

# PHP 5.6 usa Debian Stretch (EOL) — repositórios movidos para archive.debian.org
RUN echo "deb http://archive.debian.org/debian stretch main" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list && \
    apt-get -o Acquire::Check-Valid-Until=false -o Acquire::AllowInsecureRepositories=true update && \
    apt-get install -y --no-install-recommends --allow-unauthenticated \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*

# Extensões necessárias: PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Instala Composer 2.2.x — última versão LTS com suporte a PHP 5.6
# Composer 1 foi descontinuado no packagist em set/2025
RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer \
        --version=2.2.24

# Configura o DocumentRoot para backend/www
# O volume monta o projeto em /var/www/html/backend
# O Apache precisa servir /var/www/html/backend/www
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/backend/www|g' \
        /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/backend/www>|g' \
        /etc/apache2/apache2.conf 2>/dev/null || true

# Permissão de AllowOverride para o .htaccess funcionar
RUN echo '<Directory /var/www/html/backend/www>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/playlist.conf && \
    a2enconf playlist

# Copia o composer.json e instala dependências já na imagem
COPY composer.json /tmp/composer.json
RUN cd /tmp && composer install --no-dev --prefer-dist && \
    # O vendor ficará em /tmp/vendor — no entrypoint copiamos para o volume
    echo "Composer dependencies pre-installed"

# Script de inicialização: instala o Yii e ajusta permissões
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r//' /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
