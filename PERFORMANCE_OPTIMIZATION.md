# Performance Optimization Guide

## Server Specs
- 2 Core CPU, 2GB RAM, 50GB SSD
- Database: MySQL (production)
- Target: ~100 concurrent users

---

## 1. Database Indexes (DONE ✅)

Run migration to add indexes:
```bash
php artisan migrate
```

---

## 2. Production Deployment Steps

### Step 1: Copy `.env.production` to server and rename to `.env`

### Step 2: Create MySQL database
```sql
CREATE DATABASE smart_campus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'scp_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON smart_campus.* TO 'scp_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Run migrations
```bash
php artisan migrate --force
php artisan db:seed --force  # if needed
```

### Step 4: Optimize Laravel
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 3. MySQL Tuning for 2GB RAM

Create or edit `/etc/mysql/mysql.conf.d/custom.cnf`:

```ini
[mysqld]
# InnoDB Buffer Pool - 40% of RAM for shared hosting
innodb_buffer_pool_size = 768M
innodb_buffer_pool_instances = 1

# Log file size
innodb_log_file_size = 128M
innodb_log_buffer_size = 16M

# Connections (100 users + overhead)
max_connections = 150
wait_timeout = 300
interactive_timeout = 300

# Query optimization
join_buffer_size = 2M
sort_buffer_size = 2M
read_buffer_size = 1M
read_rnd_buffer_size = 1M

# Temp tables
tmp_table_size = 32M
max_heap_table_size = 32M

# Thread cache
thread_cache_size = 16

# Disable query cache (deprecated in MySQL 8)
# query_cache_type = 0
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

---

## 4. PHP-FPM Settings

Edit `/etc/php/8.x/fpm/pool.d/www.conf`:

```ini
; For 2GB RAM with ~100 users
pm = dynamic
pm.max_children = 15
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500

; Memory limit per process
php_admin_value[memory_limit] = 128M
```

Enable OPcache in `/etc/php/8.x/fpm/conf.d/10-opcache.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

---

## 5. Code Optimizations Applied ✅

- **Bulk upsert** for attendance (1 query instead of N)
- **Eager loading** with `->with()` relationships
- **Database indexes** on frequently queried columns

---

## 6. Quick Deployment Checklist

```bash
# On production server:
[ ] Copy .env.production → .env
[ ] Update DB credentials in .env
[ ] composer install --optimize-autoloader --no-dev
[ ] php artisan key:generate (if new install)
[ ] php artisan migrate --force
[ ] php artisan storage:link
[ ] php artisan optimize
[ ] Set correct permissions: chmod -R 775 storage bootstrap/cache
[ ] Configure cron: * * * * * php /path/artisan schedule:run
```

---

## 7. Expected Performance

| Metric | Before | After |
|--------|--------|-------|
| Page load | 2-4s | 0.5-1s |
| DB queries/page | 50-100 | 10-20 |
| Concurrent users | ~30 | 100+ |
| Memory per request | 64MB | 32MB |
