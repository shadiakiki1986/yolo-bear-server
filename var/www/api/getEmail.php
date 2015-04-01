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
	CLI	php getEmail.php [email] [password]

		php getEmail.php "shadiakiki1986@gmail.com" "abcdef"

	AJAX
		 $.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/getEmail.php",
		    type: 'POST',
		    data: {email0:'shadiakiki1986@gmail.com'",pwd:'123456'},
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
}

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';

try {
	if($email0==""||$pwd=="") { throw new Exception("Please enter the email and password.\n"); }

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
		throw new Exception("Invalid email {$email0}.");
	}

	$piv=array(
		"email0"=>$entry['email0']['S'],
		"nick"=>$entry['nick']['S'],
		"metaD"=>$entry['metaD']['S']
	);

	// done
	echo json_encode($piv);
} catch(Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
