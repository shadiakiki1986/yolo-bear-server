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
	CLI	php putEmail.php [email] [password (leave empty if new entry)] [nickname] [peer id] [metaData]

		php putEmail.php "shadiakiki1986@gmail.com" "" "" "" "" # registering new user wihtout any special information
		php putEmail.php "shadiakiki1986@gmail.com" "abcdef" "shadi" "" "" # update nick info

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/putEmail.php",
		    type: 'POST',
		    data: {email0:'shadiakiki1986@gmail.com'",pwd:'',nick:'',peerId:'',metaD:''},
		    success: function (data) {
			console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
			console.log("error", ts, et);
		    }
		 });
*/

//if(defined("argc") && $argc>1) {
if($argc>1) {
	$email0=$argv[1];
	$pwd=$argv[2];
	$nick=$argv[3];
	$peerId=$argv[4];
	$metaD=$argv[5];
} else {
	// Normally, the following would have been:
	// $peerId=$_POST["peerId"];
	// $nick=$_POST["nick"];
	// $pwd=$_POST["pwd"];
	// but due to some angular-php post shit
	// http://stackoverflow.com/a/15485690
	// it has to be as such
	$postdata = file_get_contents("php://input");
	$request = json_decode($postdata,true);
	$email0 = $request['email0']; # I could have just used "email" instead of "email0". "email" is not a reserved word in dynamodb, but I'm using "email0" anyway just in case it becomes a reserved word later
	$pwd = $request['pwd'];
	$nick = $request['nick'];
	$peerId = $request['peerId'];
	$metaD = $request['metaD'];
}

require_once '/etc/yolo-bear-server-config.php';
require_once ROOT.'/lib/connectDynamodb.php';
require_once ROOT.'/lib/mailSend.php';
require_once ROOT.'/lib/mailValidate.php';

try {
	if($email0=="") { throw new Exception("Please enter the email.\n"); }

	$client=connectDynamoDb();

	// Identify if nick already exists
	$entry=$client->getItem(array(
	    'TableName' => 'yolo-bear-users2',
	    'Key' => array( 'email0' => array('S' => $email0) )
	));
	$entry=(array)$entry['Item'];

	// if already existing
	if(count($entry)>0) {
		if($entry['pwd']['S']!=$pwd) {
			throw new Exception("Wrong password");
		}
	} else {
		// check if valid before sending email
		if(!mailValidate($email0)) {
			throw new Exception("Invalid email {$email0}.");
		}
		// generate random code
		$pwd=substr(uniqid(),-5,5);
	}

	$piv=array(
		"email0"=>array('S'=>$email0),
		"pwd"=>array('S'=>$pwd),
		"nick"=>array('S'=>$nick),
		"peerId"=>array('S'=>$peerId),
		"metaD"=>array('S'=>$metaD)
	);
	if($nick=="") unset($piv['nick']);
	if($peerId=="") unset($piv['peerId']);
	if($metaD=="") unset($piv['metaD']);
	$client->putItem(array(
	    'TableName' => 'yolo-bear-users2',
	    'Item' => $piv));

	if(count($entry)==0) {
		// send email about new nickname protection
		if(!mailSend($email0,
			"Yolo-bear registration",
			"Welcome to Yolo-bear.
			Your account's set password is {$pwd}."
		)) {
			throw new Exception("Failed to send email to {$email0}.");
		} else {
			echo json_encode(array('warning'=>"An email has been sent to you with the password to use."));
			return;
		}
	}

	// done
	echo "{}";
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
