#!/bin/bash
# Check document INSTALL

INSTALL_DIR=~/yolo-bear-server
APP_DIR=~/yolo-bear

apt-get install apache2 php5 php5-cli apache2-utils

cp $INSTALL_DIR/etc/yolo-bear-server-config-sample.php /etc/yolo-bear-server-config.php
vim /etc/yolo-bear-server-config.php # edit parameters

cp $INSTALL_DIR/etc/apache2/sites-available/yolo-bear-server-sample.conf /etc/apache2/sites-available/yolo-bear-server.conf # Modify file if needed
ln -s /etc/apache2/sits-available/yolo-bear-server.conf /etc/apache2/sites-enabled/yolo-bear-server.conf
service apache2 restart

# configure client app served over web
vim $APP_DIR/www/js/config.js

# AWS php SDK
wget "https://github.com/aws/aws-sdk-php/releases/download/2.7.9/aws.phar" -O /usr/share/php5/aws.phar # not sure why curl downloads only the first 300 bytes and stops
# There seems to be a bug in the aws.phar file... it cannot find itself unless a 2nd copy is put next to the caller script
ln -s /usr/share/php5/aws.phar $INSTALL_DIR/lib

