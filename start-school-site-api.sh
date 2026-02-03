#!/bin/bash

# Smart Campus School Site API Startup Script (Optimized for Mobile Development)
echo "🏫 Starting Smart Campus School Site API on port 8088..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Copying from .env.example..."
    cp .env.example .env
    echo "✅ Please configure your .env file and run this script again."
    exit 1
fi

# Quick dependency check (skip if already installed)
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
else
    echo "✅ Composer dependencies already installed"
fi

# Generate app key if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Skip migrations for faster startup (uncomment if needed)
# echo "🗄️  Running database migrations..."
# php artisan migrate --seed

# Skip frontend build for API-only development
echo "⚡ Skipping frontend build for faster API startup"

# Clear caches for development
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Start the server
echo "🌐 Starting School Site API server on http://10.180.162.219:8088"
echo "📱 API Base URL: http://10.180.162.219:8088/api"
echo "📧 Default admin: admin@novahubmm.com / password"
echo "👨‍🏫 Default teacher: teacher@novahubmm.com / password"
echo "👨‍💼 Default staff: staff@novahubmm.com / password"
echo "🛑 Press Ctrl+C to stop the server"
echo ""

php artisan serve --host=0.0.0.0 --port=8088