# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# تحديث النظام وتثبيت المكتبات اللازمة
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql
    
RUN docker-php-ext-install pdo pdo_pgsql

# تمكين mod_rewrite في Apache (مطلوب للـ Laravel routes)
RUN a2enmod rewrite

# نسخ ملفات المشروع إلى السيرفر
COPY . /var/www/html/

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# تثبيت اعتماديات Laravel (بدون dev)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# إنشاء مفتاح التطبيق تلقائياً في حالة عدم وجوده
RUN php artisan key:generate || true

# إعداد صلاحيات الملفات (مهم جداً لتفادي Permission denied)
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# تعديل إعداد Apache ليشير إلى مجلد public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# فتح المنفذ 80
EXPOSE 80

# تنفيذ migrations (إن وجدت قاعدة بيانات)
RUN php artisan migrate --force || true

# تشغيل Apache
CMD ["apache2-foreground"]
