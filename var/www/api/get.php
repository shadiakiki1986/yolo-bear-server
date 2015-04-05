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

// supporting functions for html formatting
function teamById($teamId, $phpArray) {
	$t1=array_filter($phpArray['tournamentData']['teams'], function($t) use($teamId) { return $t['id']==$teamId; });
	if(count($t1)!=1) die("Error identifying team in game"); 
	return array_values($t1)[0];
}
function playerStatsByStatName($gs,$sn) {
# $gs: gameStats field in player field
# $sn: stat name, i.e. Score, Assists, Rebounds, Steals, Blockshots
	return array_sum(array_map(function($gsi) use($sn) { return $gsi[$sn]; }, $gs));
}
function teamPlayers($teamId,$ps) {
	return array_filter($ps,function($p) use($teamId) { return $p['teamId']==$teamId; }); // players filtered by team
}
function teamStatsByStatName($teamId,$ps,$sn) {
# $teamId: team ID
# $ps: players field in tournamentData
# $sn: stat name, i.e. Score, Assists, Rebounds, Steals, Blockshots
	$ps2=teamPlayers($teamId,$ps);
	return array_sum(array_map(function($p) use($sn) { return playerStatsByStatName($p['gameStats'],$sn); }, $ps2));
}
$statNames=array("Score", "Assists", "Rebounds", "Steals", "Blockshots");
function echoAsTh($statNames) {
	foreach($statNames as $sn) echo "<th>".$sn."</th>";
}

$gameStates=array("Waiting","Done","Playing");

if($format=="html") $phpArray['tournamentData']=json_decode($phpArray['tournamentData'],true);

// output
switch($format) {
	case "json":
		echo json_encode($phpArray);
		break;
	case "html":
		// debug how this looks on facebook:  https://developers.facebook.com/tools/debug/og/object/
		echo "<html xmlns='http://www.w3.org/1999/xhtml' xmlns:og='http://ogp.me/ns#'>";

		echo "<head>";
		echo "<title>".$phpArray['tournamentName']."</title>";
		echo "<meta property='og:image' content='http://$_SERVER[HTTP_HOST]/yolo-bear-server/api/img/logo.png' />";
		echo "<meta property='og:title' content='".$phpArray['tournamentName']."' />";
		$url="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		echo "<meta property='og:url' content='$url' />";
		$descr=count($phpArray['tournamentData']['teams'])." team(s), ".
			count($phpArray['tournamentData']['players'])." player(s), ".
			count($phpArray['tournamentData']['games'])." game(s)";
		echo "<meta property='og:description' content='".$descr."' />";
		echo "<meta property='og:site_name' content='Yolo bear' />";
		echo "<meta property='og:type' content='article' />";
		echo "</head>";

		echo "<body>";
		echo "<div id='fb-root'></div>";
		// https://developers.facebook.com/docs/plugins/share-button
		// Note that I created yolo-bear as an app on facebook to get a new app ID
		echo "<script>
		  window.fbAsyncInit = function() {
		    FB.init({
		      appId      : '894833157233421',
		      xfbml      : true,
		      version    : 'v2.3'
		    });
		  };

		  (function(d, s, id){
		     var js, fjs = d.getElementsByTagName(s)[0];
		     if (d.getElementById(id)) {return;}
		     js = d.createElement(s); js.id = id;
		     js.src = '//connect.facebook.net/en_US/sdk.js';
		     fjs.parentNode.insertBefore(js, fjs);
		   }(document, 'script', 'facebook-jssdk'));
		</script>";
		echo "<img src='img/logo.png'>";
		echo "<h1>Yolo-bear tournament: ".$phpArray['tournamentName']."</h1>";
		echo "<div class='fb-share-button' data-href='$url' data-layout='button'></div>"; // button_count
		echo "<table border=1><caption>Teams (".count($phpArray['tournamentData']['teams']).")</caption>";
		echo "<tr>";
		echo "<th>Team</th>";
		echoAsTh($statNames);
		echo "<th>Players</th>";
		echo "</tr>";
		foreach($phpArray['tournamentData']['teams'] as $t) {
			echo "<tr><td>".$t['name']."</td>";
			foreach($statNames as $sn) echo "<td>".teamStatsByStatName($t['id'],$phpArray['tournamentData']['players'],$sn)."</td>";
			echo "<td>".implode(array_map(function($p) { return $p['name']; },teamPlayers($t['id'],$phpArray['tournamentData']['players'])),",")."</td>";
			echo "</tr>";
		}
		echo "</table>";

		echo "<table border=1><caption>Players (".count($phpArray['tournamentData']['players']).")</caption>";
		echo "<tr>";
		echo "<th>Team</th><th>Player</th>";
		echoAsTh($statNames);
		echo "</tr>";
		foreach($phpArray['tournamentData']['players'] as $p) {
			$t=teamById($p['teamId'],$phpArray);
			echo "<tr><td>".$t['name']."</td><td>".$p['name']."</td>";
			foreach($statNames as $sn) echo "<td>".playerStatsByStatName($p['gameStats'],$sn)."</td>";
			echo "</tr>";
		}
		echo "</table>";

		foreach($gameStates as $gs) {
			$games=array_filter($phpArray['tournamentData']['games'],function($g) use($gs) { return $g['state']==$gs; });
			if(count($games)==0) {
				echo "<div>Games: $gs - None</div>";
			} else {
				switch($gs) {
					case "Playing":
					case "Done":
						echo "<h2>Games: $gs: ".count($games)."</h2>";
						foreach($games as $g) {
							$t1=teamById($g['team1Id'],$phpArray);
							$t2=teamById($g['team2Id'],$phpArray);
							echo "<div>".$t1['name']." x ".$t2['name'];
							echo "<table border=1>";
							echo "<tr>";
							echo "<th>Team</th><th>Player</th>";
							echoAsTh($statNames);
							echo "</tr>";
							foreach(array($t1,$t2) as $tx) {
								foreach(teamPlayers($tx['id'],$phpArray['tournamentData']['players']) as $p) {
									echo "<tr><td>".$tx['name']."</td><td>".$p['name']."</td>";
									$gaa=array_filter($p['gameStats'],function($gs) use($g) { return $gs['gid']==$g['id']; });
									if(count($gaa)!=1) die("Faild to find game stats");
									foreach($statNames as $sn) echo "<td>".playerStatsByStatName($gaa,$sn)."</td>";
									echo "</tr>";
								}
							}
							echo "</table>";
							echo "</div>";
						}
						echo "</table>";
						break;
					case "Waiting":
						echo "<ul>Games: $gs (".count($games).")";
						foreach($games as $g) {
							$t1=teamById($g['team1Id'],$phpArray);
							$t2=teamById($g['team2Id'],$phpArray);
							echo "<li>".$t1['name']." x ".$t2['name']."</li>";
						}
						echo "</ul>";
						break;
					default:
						die("Invalid game state");
				}
			}
		}
		echo "</body>";
		echo "</html>";
		break;
	default:
		die("Unsupported format");
}
