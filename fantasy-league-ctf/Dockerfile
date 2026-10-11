FROM php:8.1-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Habilitar extensión SQLite para PHP
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar mod_headers y mod_rewrite
RUN a2enmod headers rewrite

# Copiar archivos de la aplicación
COPY ./src /var/www/html/

# Copiar configuración Apache
COPY ./apache-config.conf /etc/apache2/sites-available/000-default.conf

# Crear directorio para base de datos
RUN mkdir -p /var/db && chmod 777 /var/db

# Crear base de datos y poblarla
RUN sqlite3 /var/db/fantasy.db < /var/www/html/init.sql && \
    chmod 666 /var/db/fantasy.db

# Permisos
RUN chown -R www-data:www-data /var/www/html /var/db && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
