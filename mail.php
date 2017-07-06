<?php
  /* 
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	$username = 'gilquin.nicomak@gmail.com';
	$password = 'L@ur@G!lqu!n#12';
	*/

	$hostname = '{box421.bluehost.com:993/imap/ssl}INBOX.Sent';
	$username = 'gilquin@nicomak.eu';
	$password = 'gilquin007';
	
	/* try to connect */
$imap = imap_open($hostname,$username,$password) or die('Cannot connect to Bluehost: ' . imap_last_error());

/*
$emails = imap_search($imap,'ALL');

if($emails) {

	rsort($emails);

	for($i =0; $i<10; $i++) {
	
		$email_number = $emails[$i];
		$overview = imap_fetch_overview($inbox,$email_number,0);;
		
		echo '<p>';
		echo 'Subject : '.$overview[0]->subject.'<br />';
		echo 'From : '.$overview[0]->from.'<br />';
		echo 'on '.$overview[0]->date.'<br />';
		echo '</p>';
	}

} 
*/

$headers = imap_headers($imap);
$headers = array_reverse($headers);
for($i=0; $i<10; $i++){
	$header = $headers[$i];
	echo '<pre>'; var_dump($header); echo '</pre>';
	//echo 'Adresse email : '.$header->from[0]->mailbox.'@'.$header->from[0]->host;
}

imap_close($imap);

?>