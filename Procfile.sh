#!/usr/bin/env bash

# Post-deployment script for Railway
# Executed after build completes

echo "🚀 Running Laravel post-deployment commands..."

# Clear caches
echo "🧹 Clearing application caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if needed
echo "📁 Creating storage symlink..."
php artisan storage:link || true

echo "✅ Post-deployment completed successfully!"
