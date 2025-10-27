# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# تحديث النظام وتثبيت مكتبات SQLite وغيرها
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    zip unzip git curl

# تثبيت امتدادات PHP المطلوبة للـ Laravel
RUN docker-php-ext-install pdo pdo_sqlite

# تمكين mod_rewrite في Apache (مطلوب للـ Laravel routes)
RUN a2enmod rewrite

# نسخ ملفات المشروع إلى السيرفر
COPY . /var/www/html/

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# تثبيت اعتماديات Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# إعداد صلاحيات الملفات
RUN chmod -R 775 storage bootstrap/cache

# تعديل إعداد Apache ليشير إلى مجلد public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# فتح المنفذ 80
EXPOSE 80

# تشغيل Apache
CMD ["apache2-foreground"]
