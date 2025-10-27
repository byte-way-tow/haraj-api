# استخدم صورة PHP مع Composer
FROM php:8.2-apache

# تثبيت التمديدات المطلوبة
RUN docker-php-ext-install pdo pdo_sqlite

# نسخ ملفات المشروع
COPY . /var/www/html/

# إعداد صلاحيات المجلدات
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت الاعتمادات
RUN apt-get update && apt-get install -y zip unzip && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# إعداد Apache لتوجيه الطلبات إلى public/
RUN a2enmod rewrite
COPY ./public/.htaccess /var/www/html/public/.htaccess
RUN echo "DocumentRoot /var/www/html/public" > /etc/apache2/sites-available/000-default.conf

# فتح المنفذ
EXPOSE 80

CMD ["apache2-foreground"]
