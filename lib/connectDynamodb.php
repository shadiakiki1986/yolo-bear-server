<?php

require_once '/etc/yolo-bear-server-config.php';
require_once ROOT.'/lib/aws.phar';

use Aws\DynamoDb\DynamoDbClient;

function connectDynamodb() {
return 	DynamoDbClient::factory(array(
    'key' => AWS_KEY, # check config file
    'secret'  => AWS_SECRET,
    'region'  => AWS_REGION
));
}

