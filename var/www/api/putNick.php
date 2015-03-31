<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Adds/Updates a yolo-bear user nickname stored on the server

 USAGE
	CLI	php putNick.php [peer js ID] [nickname] [peer ID password for nick]
		php putNick.php "j01gtoe9ekuwstt9" "shadi" "123456"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/putNick.php",
		    type: 'POST',
		    data: {peerId:'j01gtoe9ekuwstt9',nick:'shadi',pwd:'123456"},
		    success: function (data) {
			console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
			console.log("error", ts, et);
		    }
		 });
*/

if(defined("argc")&&$argc>1) {
	$pid=$argv[1];
	$nick=$argv[2];
	$pwd=$argv[3];
} else {
	// Normally, the following would have been:
	// $pid=$_POST["peerId"];
	// $nick=$_POST["nick"];
	// $pwd=$_POST["pwd"];
	// but due to some angular-php post shit
	// http://stackoverflow.com/a/15485690
	// it has to be as such
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata,true);
	$pid = $request['peerId'];
	$nick = $request['nick'];
	$pwd = $request['pwd'];
}

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';


try {
	if($pid==""||$nick==""||$pwd=="") { throw new Exception("Please enter the PEER JS ID, nickname, and password.\n"); }

	$client=connectDynamoDb();
	$entry=$client->getItem(array(
	    'TableName' => 'yolo-bear-users',
	    'Key' => array( 'peerId' => array('S' => $pid) )
	));
	$entry=(array)$entry['Item'];

	if(count($entry)>0) {
		// check password
		if($entry['pwd']['S']!=$pwd) { throw new Exception("Wrong password."); }
	}

	$client->putItem(array(
	    'TableName' => 'yolo-bear-users',
	    'Item' => array(
		"peerId"=>array('S'=>$pid),
		"nick"=>array('S'=>$nick),
		"pwd"=>array('S'=>$pwd),
		"lastUse"=>array('S'=>date("Y-m-d"))
		)
	));

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
