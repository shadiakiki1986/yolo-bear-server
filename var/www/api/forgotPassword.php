<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Emails an email's forgotten password

 USAGE
  CLI  php forgotPassword.php [email]

    php forgotPassword.php "shadiakiki1986@gmail.com"

  AJAX
     $.ajax({
        url:"http://shadi.ly/yolo-bear-server/api/forgotPassword.php",
        type: 'POST',
        data: {email0:'shadiakiki1986@gmail.com'"},
        success: function (data) {
      console.log(data);
        },
        error: function (jqXHR, ts, et) {
      console.log("error", ts, et);
        }
     });
*/

//if(defined("argc") && $argc>1) {
if(isset($argc)) {
  if($argc>1) {
    $email0=$argv[1];
  }
} else {
  // Normally, the following would have been:
  // $peerId=$_POST["peerId"];
  // $nick=$_POST["nick"];
  // but due to some angular-php post shit
  // http://stackoverflow.com/a/15485690
  // it has to be as such
  $postdata = file_get_contents("php://input");
  $request = json_decode($postdata,true);
  $email0 = $request['email0']; # I could have just used "email" instead of "email0". "email" is not a reserved word in dynamodb, but I'm using "email0" anyway just in case it becomes a reserved word later
}

require_once dirname(__FILE__).'/../../../config.php';
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
    // check if valid before sending email
    if(!mailValidate($email0)) {
        throw new Exception("Invalid email {$email0}.");
    }

    if(!mailSend($email0,
        "Yolo Bear forgotten password",
        'Your forgotten password is '.$entry['pwd']['S']
    )) {
        throw new Exception("Failed to send email to {$email0}.");
    } else {
        echo json_encode(array('warning'=>"An email has been sent to you with the password to use. Please check your junk folder if the email is not in your inbox."));
        return;
    }
  } else {
    throw new Exception("Invalid email {$email0}.");
  }

  // done
  echo "Done";
} catch(Exception $e) {
  echo json_encode(array('error'=>$e->getMessage()));
}
