<?php

header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Deletes a yolo-bear tournament stored on the server

 USAGE
	CLI	php del.php [tournament name] [tournament password]
		php del.php "safra 2014" "1234"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/del.php",
		    type: 'POST',
		    data: {tournamentName:'safra 2014',tournamentPassword:'1234'},
		    success: function (data) {
			console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
			console.log("error", ts, et);
		    }
		 });
*/

if($argc>1) {
	$tn=$argv[1];
	$tp=$argv[2];
} else {
	$tn=$_GET["tournamentName"];
	$tp=$_GET["tournamentPassword"];
}

require_once '/etc/yolo-bear-server-config.php';
require_once ROOT.'/lib/connectDynamodb.php';

try {

	if($tn==""||$tp=="") { throw new Exception("Please enter the tournament name and password.\n"); }

	$client=connectDynamoDb();
	$entry=$client->getItem(array(
	    'TableName' => 'yolo-bear-tournaments',
	    'Key' => array( 'tournamentName'      => array('S' => $tn) )
	));
	$entry=(array)$entry['Item'];

	if(count($entry)==0) { throw new Exception("No such tournament $tn."); }

	if($entry['tournamentPassword']['S']!=$tp) { throw new Exception("Wrong password."); }

	$client->deleteItem(array(
	    'TableName' => 'yolo-bear-tournaments',
	    'Key' => array( "tournamentName"=>array('S'=>$tn) )
	));

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
