# استخدم صورة PHP الرسمية
FROM php:8.2-apache

# تثبيت الإضافات المطلوبة لـ Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# نسخ الملفات إلى داخل الحاوية
WORKDIR /var/www/html
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# إنشاء مفتاح Laravel تلقائي (إذا لم يكن موجود)
RUN php artisan key:generate || true

# إعداد أذونات التخزين
RUN chmod -R 777 storage bootstrap/cache

# فتح المنفذ الافتراضي
EXPOSE 80

# تشغيل Apache
CMD ["apache2-foreground"]
