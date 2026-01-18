# Dockerfile for Tire Fitment Finder Application
# Multi-stage build for optimized production image

# Stage 1: Build stage (if needed for future dependencies)
FROM php:8.2-cli-alpine AS builder

# Install git for composer (if needed)
RUN apk add --no-cache git

WORKDIR /app

# Copy composer files (if using composer)
COPY composer.json composer.lock* ./
# Uncomment if you install dependencies via composer:
# RUN composer install --no-dev --optimize-autoloader

# Stage 2: Production stage
FROM php:8.2-cli-alpine

# Install system dependencies and development packages for PHP extensions
RUN apk add --no-cache \
    curl \
    postgresql-dev \
    mysql-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

# Set working directory
WORKDIR /app

# Copy application files
COPY --from=builder /app /app
COPY . /app

# Create a non-root user for security
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser && \
    chown -R appuser:appuser /app

# Switch to non-root user
USER appuser

# Expose port (Render will set PORT env var)
EXPOSE 8000

# Health check - simplified to check if PHP server is responding
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD php -r "file_get_contents('http://localhost:${PORT:-8000}/healthz.php') ? exit(0) : exit(1);" || exit 1

# Start PHP built-in server from root to access both public and api directories
CMD php -S 0.0.0.0:${PORT:-8000} -t . router.php
