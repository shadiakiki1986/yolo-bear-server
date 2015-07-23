<?php

require_once dirname(__FILE__).'/../config.php';

use Aws\DynamoDb\DynamoDbClient;

function connectDynamodb() {
  $neededVars=array("AWS_KEY", "AWS_SECRET", "AWS_REGION");
  foreach($neededVars as $nv) if(!defined($nv)) throw new Exception("Please define the $nv variable in the config file");

  return DynamoDbClient::factory(array(
    'version' => 'latest',
    'region'  => AWS_REGION,
    'credentials' => array(
      'key' => AWS_KEY, # check config file
      'secret'  => AWS_SECRET,
    )
  ));
}

