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

