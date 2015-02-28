<?php
header("Access-Control-Allow-Origin: *");

/*
 Returns a selected yolo-bear tournament
 
 Usage:
 	CLI
		php get.php 'safra 2014'

 	Ajax
		$.ajax({
		    url:"http://shadi.ly/yolo-bear-server/api/get.php",
		    type: 'GET',
		    data: {tournamentName:"safra 2014"},
		    success: function (data) {
		        console.log(data);
		    },
		    error: function (jqXHR, ts, et) {
		        console.log("error", ts, et);
		    }
		 });
*/

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';

if($argc==2) {
	$tn=$argv[1];
} else {
	$tn=$_GET["tournamentName"];
}

if($tn=="") {
	echo json_encode(array('error'=>"Please pass the tournament name."));
	return;
}

# retrieval from dynamo db table
$ddb=connectDynamoDb();

$ud=$ddb->getItem(array(
    'TableName' => 'yolo-bear-tournaments',
    'Key' => array( 'tournamentName' => array('S' => $tn)),
    'AttributesToGet' => array('tournamentName','tournamentData')
));

if(count($ud['Item'])==0) {
	echo json_encode(array("error"=>"No tournament with such name $tn"));
	return;
}

// convert $ud to regular php array
$phpArray=array();
foreach($ud['Item'] as $k2=>$v2) $phpArray[$k2]=$v2['S'];

echo json_encode($phpArray);
