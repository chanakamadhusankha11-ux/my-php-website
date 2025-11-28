# Step 1: Use the official PHP image with Apache server
# We are using PHP version 8.2, which is modern and stable.
FROM php:8.2-apache

# Step 2: Install necessary system dependencies and PHP extensions
# pdo and pdo_pgsql are needed to connect to our PostgreSQL database.
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
&& docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
&& docker-php-ext-install pdo pdo_pgsql

# Step 3: Install Composer (The PHP package manager)
# This downloads the composer installer and runs it.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Step 4: Set the working directory to where Apache looks for files
WORKDIR /var/www/html

# Step 5: Copy our project files into the container
COPY . .

# Step 6: Run composer install to download PHP dependencies
# This will download the 'heroku/heroku-buildpack-php' package.
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Step 7: Fix permissions so Apache can read/write files
RUN chown -R www-data:www-data /var/www/html