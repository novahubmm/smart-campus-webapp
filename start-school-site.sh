#!/bin/bash

# Smart Campus School Site Startup Script
echo "🏫 Starting Smart Campus School Site for Mobile Development..."

# Get local IP address
LOCAL_IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -n 1)
PORT=8088

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Copying from .env.example..."
    cp .env.example .env
    echo "✅ Please configure your .env file and run this script again."
    exit 1
fi

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installing NPM dependencies..."
    npm install
fi

# Generate app key if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Run migrations
# echo "🗄️  Running database migrations..."
# php artisan migrate --seed

# Build assets
echo "� Building frontend assets..."
npm run build

# Clear cache for development
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Start the server
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 School Site Server Started!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📱 Mobile App Development URLs:"
echo "   Local:    http://localhost:${PORT}"
echo "   Network:  http://${LOCAL_IP}:${PORT}"
echo ""
echo "🔗 API Base URL for Mobile Apps:"
echo "   http://${LOCAL_IP}:${PORT}/api/v1"
echo ""
echo "👥 Default Accounts:"
echo "   📧 Admin:    admin@novahubmm.com / password"
echo "   👨‍🏫 Teacher:  teacher@novahubmm.com / password"
echo "   👨‍💼 Staff:    staff@novahubmm.com / password"
echo "   👨‍👩‍👧 Guardian: (check your database)"
echo ""
echo "📝 Update your mobile app config with:"
echo "   BASE_URL=http://${LOCAL_IP}:${PORT}"
echo ""
echo "🛑 Press Ctrl+C to stop the server"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

php artisan serve --host=0.0.0.0 --port=${PORT}