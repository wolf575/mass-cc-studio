FROM php:8.2-apache

COPY . /var/www/html/

# Ensure the data directory exists and is writable by Apache (www-data),
# so login rate-limit state and credential changes persist on the volume.
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80
