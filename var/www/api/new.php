<?php

header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Adds/Updates a yolo-bear tournament stored on the server

 USAGE
	CLI	php new.php [tournament name] [tournament password] [tournament data]
		php new.php "safra 2014" "1234" "{}"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/new.php",
		    type: 'POST',
		    data: {tournamentName:'safra 2014',tournamentPassword:'1234',tournamentData:'{}'},
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
	$td=$argv[3];
} else {
	$tn=$_GET["tournamentName"];
	$tp=$_GET["tournamentPassword"];
	$td=$_GET["tournamentData"];
}

require_once dirname(__FILE__).'/../../../config.php';
require_once ROOT.'/lib/connectDynamodb.php';

try {

	if($tn==""||$tp==""||$td=="") { throw new Exception("Please enter the tournament name, password, and data.\n"); }

	$client=connectDynamoDb();
	$entry=$client->getItem(array(
	    'TableName' => 'yolo-bear-tournaments',
	    'Key' => array( 'tournamentName'      => array('S' => $tn) )
	));
	$entry=(array)$entry['Item'];

	if(count($entry)>0) {
		// check password
		if($entry['tournamentPassword']['S']!=$tp) { throw new Exception("Wrong password."); }
	}

	$client->putItem(array(
	    'TableName' => 'yolo-bear-tournaments',
	    'Item' => array(
		"tournamentName"=>array('S'=>$tn),
		"tournamentPassword"=>array('S'=>$tp),
		"tournamentData"=>array('S'=>$td)
		)
	));

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
