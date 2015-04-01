<?php

require_once '/etc/zboota-server-config.php';

function mailSend($to,$subj,$body) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://api.mailgun.net/v2/".MAILGUN_DOMAIN."/messages");
	curl_setopt($curl, CURLOPT_USERPWD, "api:".MAILGUN_KEY);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_POSTFIELDS, array(
		'from'=>MAILGUN_FROM,
		'to'=>$to,
		'subject'=>$subj,
		'html'=>$body
	));

	$result = curl_exec($curl);
	curl_close($curl);

	if($result=="Forbidden") throw new Exception("Forbidden to send email. Did you update the mailgun parameters in /etc/zboota-server-config.php?\n");

	return json_decode($result,true)['message']=="Queued. Thank you.";
}
