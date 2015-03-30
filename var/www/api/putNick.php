<?php

header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Adds/Updates a yolo-bear user nickname stored on the server

 USAGE
	CLI	php putNick.php [peer js ID] [nickname]
		php putNick.php "j01gtoe9ekuwstt9" "shadi"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/putNick.php",
		    type: 'GET',
		    data: {peerId:'j01gtoe9ekuwstt9',nick:'shadi'},
		    success: function (data) {
			console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
			console.log("error", ts, et);
		    }
		 });
*/

if($argc>1) {
	$pid=$argv[1];
	$nick=$argv[2];
} else {
	$pid=$_GET["peerId"];
	$nick=$_GET["nick"];
}

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';

try {

	if($pid==""||$nick=="") { throw new Exception("Please enter the PEER JS ID and nickname.\n"); }

	$client=connectDynamoDb();
	$entry=$client->getItem(array(
	    'TableName' => 'yolo-bear-users',
	    'Key' => array( 'peerId' => array('S' => $pid) )
	));
	$entry=(array)$entry['Item'];

	if(count($entry)>0) {
		// check password
		//if($entry['tournamentPassword']['S']!=$tp) { throw new Exception("Wrong password."); }
	}

	$client->putItem(array(
	    'TableName' => 'yolo-bear-users',
	    'Item' => array(
		"peerId"=>array('S'=>$pid),
		"nick"=>array('S'=>$nick),
		"lastUse"=>array('S'=>date("Y-m-d"))
		)
	));

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
