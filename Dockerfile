# Use the official PHP image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Expose Apache port
EXPOSE 80
