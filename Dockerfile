# Use the official Heroku PHP image which includes Apache and Composer
FROM heroku/php:22-apache

# Copy the application code into the container
# The source directory on your computer is copied to /app in the container
COPY . /app

# Set the working directory
WORKDIR /app

# Run composer install to get all the dependencies
# This will also create the vendor/bin/heroku-php-apache2 file
RUN composer install --no-dev --optimize-autoloader

# This command will be run when the container starts
# It starts the Apache web server
# The CMD is already defined in the base image, so we don't need to specify it.
# CMD ["/app/vendor/bin/heroku-php-apache2", "/app/"] 
# Let's comment out the CMD line as the base image might handle it.