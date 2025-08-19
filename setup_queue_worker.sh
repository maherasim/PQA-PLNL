#!/bin/bash

echo "🚀 Laravel Queue Worker Setup Script"
echo "====================================="

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ This script must be run as root (use sudo)"
    exit 1
fi

# Get project path
read -p "Enter your Laravel project path (e.g., /var/www/yourproject): " PROJECT_PATH

if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ Project path does not exist: $PROJECT_PATH"
    exit 1
fi

# Install supervisor if not installed
if ! command -v supervisord &> /dev/null; then
    echo "📦 Installing supervisor..."
    apt-get update
    apt-get install -y supervisor
fi

# Create supervisor config
CONFIG_FILE="/etc/supervisor/conf.d/laravel-worker.conf"
echo "📝 Creating supervisor configuration..."

cat > "$CONFIG_FILE" << EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Set proper permissions
chown root:root "$CONFIG_FILE"
chmod 644 "$CONFIG_FILE"

# Create log file
touch "$PROJECT_PATH/storage/logs/worker.log"
chown www-data:www-data "$PROJECT_PATH/storage/logs/worker.log"

# Reload supervisor
echo "🔄 Reloading supervisor..."
supervisorctl reread
supervisorctl update
supervisorctl start laravel-worker:*

# Check status
echo "✅ Queue worker setup complete!"
echo "📊 Status:"
supervisorctl status laravel-worker:*

echo ""
echo "🎉 Your queue worker is now running automatically!"
echo "💡 It will start automatically on server restart."
echo "📝 Logs are available at: $PROJECT_PATH/storage/logs/worker.log"
echo ""
echo "🔧 Useful commands:"
echo "  Check status: sudo supervisorctl status laravel-worker:*"
echo "  Restart: sudo supervisorctl restart laravel-worker:*"
echo "  Stop: sudo supervisorctl stop laravel-worker:*"