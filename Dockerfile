FROM php:8.3.2

# Install dependencies
RUN apt-get update -y && apt-get install -y openssl zip unzip git libonig-dev

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
# RUN docker-php-ext-install pdo mbstring pdo_pgsql
RUN docker-php-ext-install pdo mbstring

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install dependencies using Composer
RUN composer install

# Expose port and run the application
EXPOSE 8181
CMD php artisan serve --host=0.0.0.0 --port=8181

