<?php

# Copy this file to /etc/yolo-bear-server-config.php
# and edit it with the proper parameter values
# yolo-bear-server/install.sh will already copy it and propose editing

# Root directory of installation of yolo-bear-server
define("ROOT", "/home/shadi/Development/yolo-bear-server");

# AWS connection information
define('AWS_KEY','abcdefghi');
define('AWS_SECRET','abcdefghi');
define('AWS_REGION','abcdefghi');

# Mailgun key and domain
define('MAILGUN_KEY','abcdef');
define('MAILGUN_DOMAIN','abcdef.mailgun.org');
define('MAILGUN_FROM','Zboota <postmaster@abcdef.mailgun.org>');
define('MAILGUN_PUBLIC_KEY','pubkey-abcdef');

