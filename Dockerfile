FROM php:8.2-apache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY . /var/www/html/

# Persistent data lives outside the webroot at /data (Qinglong-style),
# owned by www-data so credentials / session secret / rate-limit state persist.
RUN mkdir -p /data && chown -R www-data:www-data /data \
    && rm -f /var/www/html/docker-entrypoint.sh /var/www/html/.gitattributes

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80
