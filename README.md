# yolo-bear-server
* Server-side for app at https://github.com/shadiakiki1986/yolo-bear
* Includes running the peerjs server and saving tournaments to server (dynamodb database)

# Run
* sudo npm install peer -g
* peerjs --port 9000 --key peerjs --path /yolo-bear --debug --allow_discovery
  * could use `openssl rand -base64 18` to generate random string for key

# Install
```
apt-get update
apt-get install apache2 php5 php5-cli apache2-utils
apt-get install git
git clone https://shadiakiki1986@github.org/shadiakiki1986/yolo-bear-server
cd yolo-bear-server
composer install
cp config-sample.php config.php
vim config.php
cp etc/apache2/sites-available/yolo-bear-server-sample.conf /etc/apache2/sites-available/yolo-bear-server.conf
vim /etc/apache2/sites-available/yolo-bear-server.conf
ln -s /etc/apache2/sits-available/yolo-bear-server.conf /etc/apache2/sites-enabled/yolo-bear-server.conf
service apache2 restart
```
* Finally, configure client app served over web: vim $APP_DIR/www/js/config.js

# Uninstall
<code>
sudo rm /etc/yolo-bear-server-config.php 
sudo rm /etc/apache2/conf-enabled/yolo-bear-server.conf 
sudo rm /etc/apache2/conf-available/yolo-bear-server.conf 
rm -rf ~/yolo-bear-server
</code>
