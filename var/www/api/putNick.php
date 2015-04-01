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
	CLI	php putNick.php [peer js ID] [nickname] [peer ID password for nick] [user email to protect nick (leave empty if undesired)]
		php putNick.php "j01gtoe9ekuwstt9" "shadi" "123456" "shadiakiki1986@gmail.com"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/putNick.php",
		    type: 'POST',
		    data: {peerId:'j01gtoe9ekuwstt9',nick:'shadi',pwd:'a1b12h',email0:'shadiakiki1986@gmail.com'"},
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
	$email0=$argv[4];
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
	$email0 = $request['email0']; # I could have just used "email" instead of "email0". "email" is not a reserved word in dynamodb, but I'm using "email0" anyway just in case it becomes a reserved word later
}

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';
require_once ROOT.'/lib/mailSend.php';
require_once ROOT.'/lib/mailValidate.php';

try {
	if($pid==""||$nick==""||$pwd=="") { throw new Exception("Please enter the PEER JS ID, nickname, and password.\n"); }

        // check if valid before sending email
	$pwd2="";
	if($email0!='' && !mailValidate($email0)) {
		throw new Exception("Invalid email {$email0}.");
	}

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

	if($email0!="") {
		// generate random code
		$pwd2=substr(uniqid(),-5,5);
	}

	$client->putItem(array(
	    'TableName' => 'yolo-bear-users',
	    'Item' => array(
		"peerId"=>array('S'=>$pid),
		"nick"=>array('S'=>$nick),
		"pwd"=>array('S'=>$pwd),
		"email0"=>array('S'=>$email0),
		"pwd2"=>array('S'=>$pwd2),
		"lastUse"=>array('S'=>date("Y-m-d"))
		)
	));

	if($email0!='') {
		// send email
		if(!mailSend($email0,
			"Yolo-bear registration",
			"Welcome to Yolo-bear.
			The password protecting your nickname is {$pwd2}."
		)) {
			echo json_encode(array('error'=>"Failed to send email to {$email0}."));
			return;
		}
	}

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
