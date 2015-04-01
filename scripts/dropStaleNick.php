<?php

/*
 Drops nicknames from server that are stale

 USAGE
	CLI	php dropStaleNick.php

*/

require_once '/etc/yolo-bear-server-config.php';
require_once 'aws.phar';
require_once ROOT.'/lib/connectDynamodb.php';

$client=connectDynamoDb();

# identify nicknames more than 1 day old and that are not protected
$un=$client->getIterator('Scan',array(
    'TableName' => 'yolo-bear-users',
    'ScanFilter' => array(
	'lastUse' => array(
	    'AttributeValueList' => array(
		array('S' => date("Y-m-d")) # date("Y-m-d",strtotime('-30 days'))
	    ),
	    'ComparisonOperator' => 'LT'
	),
	'email0' => array(
	    'AttributeValueList' => array(
		array('S' => "")
	    ),
	    'ComparisonOperator' => 'NE'
	)

    )
));
$un=iterator_to_array($un);

if(count($un)==0) {
	echo "No stale unprotected users in yolo-bear-users table.\n";
} else {

	foreach ($un as $item) {
		echo "Deleting ".$item['peerId']['S'].", ".$item['nick']['S'].", ".$item['lastUse']['S']."\n";
		$client->deleteItem(array(
			'TableName' => 'yolo-bear-users',
			'Key' => array(
			    'peerId'   => array('S' => $item['peerId']['S'])
			)
		));
	}
}
