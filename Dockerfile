FROM php:8.2-apache  

#Next command is from chulka/baikal-docker/apache-php8.2.dockerfile
RUN apt-get update  && apt-get install -y \
    libcurl4-openssl-dev      \
    msmtp msmtp-mta           \
    libpq-dev                 &&\
  rm -rf /var/lib/apt/lists/* &&\
  docker-php-ext-install curl pdo pdo_mysql pdo_pgsql pgsql

RUN apt-get update && apt-get install -y unzip git sqlite3 default-mysql-client vim wget
#(vim, wget for troubleshooting while testing)
#(mysql client for running table creation script)
#(git for Hydrogen framework install)
#(unzip for composer)

#Copy the Baikal files to the Docker container
RUN mkdir /var/www/Core
RUN mkdir /var/www/Specific
RUN mkdir /var/www/config
COPY ./Core/  /var/www/Core/ 
COPY ./Specific/  /var/www/Specific/
COPY ./config/  /var/www/config/
COPY ./composer.json  /var/www/
COPY ./wait_and_seed.sh  /var/www/
COPY ./Makefile  /var/www/ 
COPY ./init-db.php  /var/www/ 
COPY ./html/ /var/www/html/
RUN mkdir /var/www/html/data
RUN mkdir /var/www/html/public
RUN chmod 755 /var/www/wait_and_seed.sh
#get composer for PHP
WORKDIR /var/www
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Add Composer to the PATH
ENV PATH="$PATH:/usr/local/bin"
#Run the Baikal composer.json file
RUN composer install
#RUN composer require sabre/dav ~4.7.0
#RUN composer require sabre/vobject ~4.5

#Get Hydrogen framework
WORKDIR /var/www/html 
RUN git clone https://github.com/buckaroo-labs/Hydrogen.git

#Fix the broken links (probably due to this repo having been cloned to
# a Windows laptop)

#Commented below is Baikal 0.11's link; we replaced it with a file
#RUN rm index.php
#RUN ln -s ../Core/Frameworks/Baikal/WWWRoot/index.php
RUN rm admin/install/index.php
WORKDIR /var/www/html/admin/install
RUN ln -s ../../../Core/Frameworks/BaikalAdmin/WWWRoot/install/index.php
WORKDIR /var/www/html/res
RUN rm core
RUN ln -s ../../Core/Resources/Web core
WORKDIR /var/www/Core/Resources/Web
RUN rm BaikalAdmin
RUN ln -s ../../Frameworks/BaikalAdmin/Resources BaikalAdmin
WORKDIR /var/www/html/res/core
RUN rm Baikal
RUN ln -s ../../Frameworks/Baikal/Resources Baikal
RUN rm TwitterBootstrap
RUN ln -s ../../Frameworks/TwitterBootstrap 
WORKDIR /var/www/html/admin
RUN rm index.php
RUN ln -s ../../Core/Frameworks/BaikalAdmin/WWWRoot/index.php
RUN mkdir /var/www/DAVUserHome
# Make all the copied files accessible by web server
WORKDIR /var/www 
RUN chown -R www-data:www-data *

# A couple more necessary installations
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# See /var/www/Core/Frameworks/BaikalAdmin/WWWRoot/install
# We've pre-configured the installation as follows by
# setting the time zone, auth type, DB back end and admin_passwordhash 
# (password='mescaler0s') in /var/www/config/baikal.yaml (copied above)





