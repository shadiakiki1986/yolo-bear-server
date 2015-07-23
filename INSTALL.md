=Requirements=
* linux server with public IP and URL to be set in mobile app
  * check variable "YOLOBEAR_SERVER_URL" in yolo-bear/www/js/common.js

# Run
* sudo npm install peer -g
* peerjs --port 9000 --key peerjs --path /yolo-bear --debug --allow_discovery
  * could use `openssl rand -base64 18` to generate random string for key

=Steps=
* apt-get update
* apt-get install git
* git clone https://shadiakiki1986@bitbucket.org/shadiakiki1986/yolo-bear-server
* cd yolo-bear-server
* composer install
* Run install.sh

=Uninstall=
<code>
sudo rm /etc/yolo-bear-server-config.php 
sudo rm /etc/apache2/conf-enabled/yolo-bear-server.conf 
sudo rm /etc/apache2/conf-available/yolo-bear-server.conf 
rm -rf ~/yolo-bear-server
</code>
