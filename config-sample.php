<?php

# Copy this file to config.php in the root installed folder
# and edit it with the proper parameter values

# Root directory of installation of yolo-bear-server
define("ROOT", dirname(__FILE__));
require ROOT.'/vendor/autoload.php'; #  if this line throw an error, I probably forgot to run composer install

# AWS connection information
define('AWS_KEY','abcdefghi');
define('AWS_SECRET','abcdefghi');
define('AWS_REGION','abcdefghi');

# Mailgun key and domain
define('MAILGUN_KEY','abcdef');
define('MAILGUN_DOMAIN','abcdef.mailgun.org');
define('MAILGUN_FROM','Zboota <postmaster@abcdef.mailgun.org>');
define('MAILGUN_PUBLIC_KEY','pubkey-abcdef');

