# Use the official Heroku PHP image which includes Apache and Composer
FROM heroku/heroku:22-php

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
CMD ["/app/vendor/bin/heroku-php-apache2", "/app/"]