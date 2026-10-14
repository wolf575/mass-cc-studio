FROM php:8.2-apache

COPY . /var/www/html/

# Persistent data lives outside the webroot at /data (Qinglong-style),
# owned by www-data so credentials / session secret / rate-limit state persist.
RUN mkdir -p /data && chown -R www-data:www-data /data

EXPOSE 80
