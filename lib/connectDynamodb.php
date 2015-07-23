<?php

require_once '/etc/yolo-bear-server-config.php';

use Aws\DynamoDb\DynamoDbClient;

function connectDynamodb() {
return 	DynamoDbClient::factory(array(
    'version' => 'latest',
    'key' => AWS_KEY, # check config file
    'secret'  => AWS_SECRET,
    'region'  => AWS_REGION
));
}

