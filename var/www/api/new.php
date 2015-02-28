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
	// http://stackoverflow.com/a/23810374
	if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
	    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
	}

	$tn=$_POST["tournamentName"];
	$tp=$_POST["tournamentPassword"];
	$td=$_POST["tournamentData"];
}

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
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
