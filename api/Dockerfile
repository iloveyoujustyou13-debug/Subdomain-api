FROM php:8.2-apache

# cURL এক্সটেনশন ইনস্টল করুন (Hostinger API-তে কল করার জন্য)
RUN docker-php-ext-install curl

# Apache-এর DocumentRoot কে 'api' ফোল্ডারে নির্দেশ করুন
ENV APACHE_DOCUMENT_ROOT /var/www/html/api
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# আপনার PHP ফাইল কপি করুন
COPY ./api /var/www/html/api
