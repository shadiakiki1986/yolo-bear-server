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

require_once dirname(__FILE__).'/../../../config.php';
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

// retrieve registered addresses
$ud2=$ddb->scan(array(
    'TableName' => 'yolo-bear-users2',
    'AttributesToGet' => array('email0','nick','peerId'),
    'ScanFilter' => array(
        'peerId' => array(
               'ComparisonOperator' => 'NOT_NULL'
        )
   )
));
$ud2=iterator_to_array($ud2);
$ud2=$ud2['Items'];

// convert $ud to regular php array, and merge with entryes from ud2 to show "registered" nicks
$phpArray=array();
foreach($ud['Items'] as $v) {
	$v2=array_filter($ud2,function($x) use($v) { return $x['peerId']['S']==$v['peerId']['S'] && $x['nick']['S']==$v['nick']['S']; });
	$v3="";
	if(count($v2)>1) { throw new Exception("WTF"); } else if(count($v2)>0) { $v3=array_values($v2)[0]['email0']['S']; }
	$phpArray[$v['peerId']['S']]=array('nick'=>$v['nick']['S'],'email0'=>$v3);
}

echo json_encode($phpArray);
