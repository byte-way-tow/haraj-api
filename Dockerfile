# صورة PHP مع Apache
FROM php:8.2-apache

# تثبيت المكتبات المطلوبة للـ PostgreSQL و Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql

# تمكين mod_rewrite للروابط في Laravel
RUN a2enmod rewrite

# نسخ المشروع إلى السيرفر
COPY . /var/www/html/
WORKDIR /var/www/html

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# تثبيت اعتماديات Laravel بدون dev
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# توليد مفتاح التطبيق
RUN php artisan key:generate || true

# إعطاء الصلاحيات المناسبة
RUN chmod -R 777 storage bootstrap/cache

# توجيه Apache إلى مجلد public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

# تشغيل الـ migrations ثم seeders ثم Apache
CMD php artisan migrate --force && php artisan db:seed --force && apache2-foreground
