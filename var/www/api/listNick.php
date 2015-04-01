<?php
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Returns a list of all yolo-bear user nicknames
 
 Usage:
 	CLI
		php listNick.php

 	Ajax
		$.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/listNick.php",
		    type: 'GET',
		    success: function (data) {
		        console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
		        console.log("error", ts, et);
		    }
		 });
*/

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';

# retrieval from dynamo db table
$ddb=connectDynamoDb();
$data=array();
$ud=$ddb->scan(array(
    'TableName' => 'yolo-bear-users',
    'AttributesToGet' => array('peerId','nick')
));
$ud=iterator_to_array($ud);

if(count($ud['Items'])==0) {
	echo json_encode(array());
	return;
}

// convert $ud to regular php array
$phpArray=array();
foreach($ud['Items'] as $v) $phpArray[$v['peerId']['S']]=$v['nick']['S'];

echo json_encode($phpArray);