<?php
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 Returns a selected yolo-bear tournament
 
 Usage:
 	CLI
		php get.php 'safra 2014'
		php get.php 'test tournament' 'html'

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

if($argc>1) {
	$tn=$argv[1];
	$format=$argv[2];
} else {
	$tn=$_GET["tournamentName"];
	$format=$_GET["format"];
}

if($tn=="") {
	echo json_encode(array('error'=>"Please pass the tournament name."));
	return;
}

if($format=="") $format="json";
if(!in_array($format,array("json","html"))) die("Unsupported format. Please use: html, json");

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

// supporting function
function teamById($teamId, $phpArray) {
	$t1=array_filter($phpArray['tournamentData']['teams'], function($t) use($teamId) { return $t['id']==$teamId; });
	if(count($t1)!=1) die("Error identifying team in game"); 
	return array_values($t1)[0];
}

if($format=="html") $phpArray['tournamentData']=json_decode($phpArray['tournamentData'],true);

// output
switch($format) {
	case "json":
		echo json_encode($phpArray);
		break;
	case "html":
		echo "<h1>Yolo-bear tournament: ".$phpArray['tournamentName']."</h1>";
		echo "<table border=1><caption>Teams (".count($phpArray['tournamentData']['teams']).")</caption>";
		foreach($phpArray['tournamentData']['teams'] as $t) echo "<tr><td>".$t['name']."</td></tr>";
		echo "</table>";
		echo "<table border=1><caption>Players (".count($phpArray['tournamentData']['players']).")</caption>";
		foreach($phpArray['tournamentData']['players'] as $p) echo "<tr><td>".$p['name']."</td></tr>";
		echo "</table>";
		echo "<table border=1><caption>Games (".count($phpArray['tournamentData']['games']).")</caption>";
		foreach($phpArray['tournamentData']['games'] as $g) {
			$t1=teamById($g['team1Id'],$phpArray);
			$t2=teamById($g['team2Id'],$phpArray);
			echo "<tr><td>".$t1['name']." x ".$t2['name']."(".$g['state'].")</td></tr>";
		}
		echo "</table>";
		break;
	default:
		die("Unsupported format");
}
