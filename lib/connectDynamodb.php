<?php

require_once dirname(__FILE__).'/../config.php';

use Aws\DynamoDb\DynamoDbClient;

function connectDynamodb() {
return 	DynamoDbClient::factory(array(
    'version' => 'latest',
    'key' => AWS_KEY, # check config file
    'secret'  => AWS_SECRET,
    'region'  => AWS_REGION
));
}

