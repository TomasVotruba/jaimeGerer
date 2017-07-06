<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class MailevaController extends Controller
{
	public function fichierDeCommande($path, $fileName){
   	
		$pdf = $path.$fileName.'.001';	
		$xml = $path.$fileName.'.002';
	   	$extension = '.tmp'; //sera renommé en .cou après le transfert FTP
	   	$file = $path.$fileName.$extension;
	   	
	   	try{
		   	$content = "CLIENT_ID=nicomak.gilquin\r\n";
		   	file_put_contents($file, $content, FILE_APPEND);
		   	$content = "GATEWAY=PAPER\r\n";
		   	file_put_contents($file, $content, FILE_APPEND);
		   	$content = "NB_FILE=2\r\n";
		   	file_put_contents($file,$content, FILE_APPEND);
		   	$content = "FILE_SIZE_1=".filesize($pdf)."\r\n";
		   	file_put_contents($file,$content, FILE_APPEND);
		   	$content = "FILE_SIZE_2=".filesize($xml)."\r\n";
		   	file_put_contents($file,$content, FILE_APPEND);
	   	} catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}

		return $file;
	   	
	}
   
	public function pieceJointeDeService($path, $fileName, $facture, $numRelance, $recommande){
	   	
	   	$extension = '.002';
	   	$file = $path.$fileName.$extension;
	   	
	   	try{
	   		$xml = new  \DOMDocument('1.0', 'iso-8859-2');
	   	
		   	//campaign
		   	$xml_campaign = $xml->createElement('pjs:Campaign');
		   	
		   	$xml_campaign->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:com', 'http://www.maileva.fr/CommonSchema');
		   	$xml_campaign->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:pjs', 'http://www.maileva.fr/MailevaPJSSchema');
		   	$xml_campaign->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:spec', 'http://www.maileva.fr/MailevaSpecificSchema');
		   	$xml_campaign->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		   	
		   	$nomCampagne = 'R'.$facture->getNum().'-'.$numRelance;
		   	$xml_track_id = $xml->createAttribute('TrackId');
		   	$xml_track_id->value = $nomCampagne;
		   	$xml_campaign->appendChild($xml_track_id);
		   	
		   	$xml_version = $xml->createAttribute('Version');
		   	$xml_version->value = "1.0";
		   	$xml_campaign->appendChild($xml_version);
		   	
		   	//requests
		   	$xml_requests = $xml->createElement('pjs:Requests');
		   		
		   	//request
		   	$xml_request = $xml->createElement('pjs:Request');
		   		
		   	$xml_media_type = $xml->createAttribute('MediaType');
		   	$xml_media_type->value = "PAPER";
		   	$xml_request->appendChild($xml_media_type);
		   		
		   	$xml_track_id = $xml->createAttribute('TrackId');
		   	$xml_track_id->value = $nomCampagne;
		   	$xml_request->appendChild($xml_track_id);
		   		
		   	//recipients
		   	$xml_recipients =  $xml->createElement('pjs:Recipients');
		   		
		   	$xml_internal =  $xml->createElement('pjs:Internal');
		   	
		   	//recipient
		   	$xml_recipient = $xml->createElement('pjs:Recipient');
		   	
		   	$xml_id = $xml->createAttribute('Id');
		   	$xml_id->value = "1";
		   	$xml_recipient->appendChild($xml_id);
		   	
		   	$xml_track_id = $xml->createAttribute('TrackId');
		   	$xml_track_id->value = $facture->getCompte();
		   	$xml_recipient->appendChild($xml_track_id);
		   	
		   	//paper address
		   	$xml_paper_address = $xml->createElement("com:PaperAddress");
		   	
		   	$xml_address_lines = $xml->createElement("com:AddressLines");
		   		
		   	$xml_address_line_1 = $xml->createElement("com:AddressLine1", $facture->getCompte()->getNom());
		   	$xml_address_lines->appendChild($xml_address_line_1);
		   		
		   	if($facture->getContact()){
		   		$xml_address_line_2 = $xml->createElement("com:AddressLine2", $facture->getContact()->getNom().' '.$facture->getContact()->getPrenom());
		   		$xml_address_lines->appendChild($xml_address_line_2);
		   	}
		   	
		   	$xml_address_line_4 = $xml->createElement("com:AddressLine4", $facture->getAdresse());
		   	$xml_address_lines->appendChild($xml_address_line_4);
		   	
		   	$codePostal = str_replace(' ','', $facture->getCodePostal());
		   	$xml_address_line_6 = $xml->createElement("com:AddressLine6", $codePostal.' '.$facture->getVille());
		   	$xml_address_lines->appendChild($xml_address_line_6);
		   	
		   	$xml_paper_address->appendChild($xml_address_lines);
		   		
		   	$xml_country = $xml->createElement("com:Country", $facture->getPays());
		   	$xml_paper_address->appendChild($xml_country);
		   	
		   	$xml_recipient->appendChild($xml_paper_address);
		   	
		   	$xml_internal->appendChild( $xml_recipient );
		   		
		   		
		   	$xml_recipients->appendChild( $xml_internal );
		   	
		   	$xml_request->appendChild( $xml_recipients );
		   	
		   	//sender
		   	$xml_senders = $xml->createElement('pjs:Senders');
		   	
		   	$xml_sender = $xml->createElement('pjs:Sender');
		   		
		   	$xml_sender_id = $xml->createAttribute('Id');
		   	$xml_sender_id->value =  $this->getUser()->getCompany()->getNom();
		   	$xml_sender->appendChild($xml_sender_id);
		   		
		   	//paper address
		   	$xml_paper_address = $xml->createElement("com:PaperAddress");
		   	
		   	$xml_address_lines = $xml->createElement("com:AddressLines");
		   	
		   	$xml_address_line_1 = $xml->createElement("com:AddressLine1", $this->getUser()->getCompany()->getNom());
		   	$xml_address_lines->appendChild($xml_address_line_1);
		   	
		   	$xml_address_line_2 = $xml->createElement("com:AddressLine2", $this->getUser()->getFirstname().' '.$this->getUser()->getLastname());
		   	$xml_address_lines->appendChild($xml_address_line_2);
		   		
		   	$xml_address_line_4 = $xml->createElement("com:AddressLine4", $this->getUser()->getCompany()->getAdresse());
		   	$xml_address_lines->appendChild($xml_address_line_4);
		   		
		   	$codePostal = str_replace(' ','', $this->getUser()->getCompany()->getCodePostal());
		   	$xml_address_line_6 = $xml->createElement("com:AddressLine6", $codePostal.' '.$this->getUser()->getCompany()->getVille());
		   	$xml_address_lines->appendChild($xml_address_line_6);
		   		
		   	$xml_paper_address->appendChild($xml_address_lines);
		   		
		   	$xml_country = $xml->createElement("com:Country", $this->getUser()->getCompany()->getPays());
		   	$xml_paper_address->appendChild($xml_country);
		   		
		   	$xml_sender->appendChild($xml_paper_address);
		   		
		   	$xml_senders->appendChild( $xml_sender );
		   	
		   	$xml_request->appendChild( $xml_senders );
		   	
		   	//options
		   	$xml_options = $xml->createElement('pjs:Options');
		   	
		   	$xml_request_options = $xml->createElement('pjs:RequestOption');
		   	
		   	$xml_paper_option = $xml->createElement("spec:PaperOption");
		   	
		   	$xml_fold_option = $xml->createElement("spec:FoldOption");
		   		
		   	if($recommande){
		   		//envoi en recommandé avec AR
		   		$xml_postage_class = $xml->createElement("spec:PostageClass", 'LRE_AR');
		   	} else {
		   		//envoi standard
		   		$xml_postage_class = $xml->createElement("spec:PostageClass", 'SLOW');
		   	}
		   	$xml_fold_option->appendChild( $xml_postage_class );
		   		
		   	$xml_fold_print_color = $xml->createElement("spec:FoldPrintColor", 'true');
		   	$xml_fold_option->appendChild( $xml_fold_print_color );
		   		
		   	$xml_paper_option->appendChild( $xml_fold_option );
		   	
		   	$xml_request_options->appendChild( $xml_paper_option );
		   		
		   	$xml_options->appendChild( $xml_request_options );
		   		
		   	$xml_request->appendChild( $xml_options );
		   	
		   	//notification
		   	$xml_notifications = $xml->createElement('pjs:Notifications');
		   	
		   	$xml_notification = $xml->createElement("pjs:Notification");
		   		
		   	$xml_notification_type = $xml->createAttribute('Type');
		   	$xml_notification_type->value = 'GENERAL';
		   	$xml_notification->appendChild($xml_notification_type);
		   	
		   	$xml_format = $xml->createElement("spec:Format", "TXT");
		   	$xml_notification->appendChild( $xml_format );
		   	
		   	$xml_protocols = $xml->createElement("spec:Protocols");
		   	
		   	$xml_protocol = $xml->createElement("spec:Protocol");
		   	
		   	$xml_email = $xml->createElement("spec:Email", $this->getUser()->getEmail());
		   	$xml_protocol->appendChild( $xml_email );
		   	
		   	$xml_protocols->appendChild( $xml_protocol );
		   		
		   	$xml_notification->appendChild( $xml_protocols );
		   		
		   	$xml_notifications->appendChild( $xml_notification );
		   	
		   	$xml_notification_lre = $xml->createElement("pjs:Notification");
		   	 
		   	$xml_notification_lre_type = $xml->createAttribute('Type');
		   	$xml_notification_lre_type->value = 'LRE';
		   	$xml_notification_lre->appendChild($xml_notification_lre_type);
		   	
		   	$xml_format_lre = $xml->createElement("spec:Format", "TXT");
		   	$xml_notification_lre->appendChild( $xml_format_lre );
		   	
		   	$xml_protocols_lre = $xml->createElement("spec:Protocols");
		   	
		   	$xml_protocol_lre = $xml->createElement("spec:Protocol");
		   	
		   	$xml_email_lre = $xml->createElement("spec:Email", $this->getUser()->getEmail());
		   	$xml_protocol_lre->appendChild( $xml_email_lre );
		   	
		   	$xml_protocols_lre->appendChild( $xml_protocol_lre );
		   	 
		   	$xml_notification_lre->appendChild( $xml_protocols_lre );
		   	 
		   	$xml_notifications->appendChild( $xml_notification_lre );
		   	
		   	$xml_request->appendChild( $xml_notifications );
		   	
		   	$xml_requests->appendChild( $xml_request );
		   		
		   	$xml_campaign->appendChild( $xml_requests );
		   	
		   	//user
		   	$xml_user = $xml->createElement('pjs:User');
		   	
		   	$xml_auth_type = $xml->createAttribute('AuthType');
		   	$xml_auth_type->value = "PLAINTEXT";
		   	$xml_user->appendChild($xml_auth_type);
		   	
		   	$xml_login = $xml->createElement('pjs:Login', 'nicomak.gilquin');
		   	$xml_user->appendChild( $xml_login );
		   	
		   	$xml_password = $xml->createElement('pjs:Password', '594E2LK');
		   	$xml_user->appendChild( $xml_password );
		   		
		   	$xml_campaign->appendChild( $xml_user );
		   		
		   	$xml->appendChild( $xml_campaign );
		   	
		   	$xml->save($file);
	   	} catch(\Exception $e){
	   		throw new \Exception($e->getMessage());
	   	}
	}

	public function depotFTP($path, $fileName){
		
		$host = 'ftp.maileva.com';
		$username = 'mlv-p-nicomak.gilquin';
		$password = 'I9nd5Fz';
		
		try{
		
			$connexion = ftp_connect($host) or die("Connexion au serveur FTP de Maileva impossible");
			$login_result = ftp_login($connexion, $username, $password );
				
			//put each file on the FTP
			$arr_extentsions = array('.001','.002','.tmp');
			foreach($arr_extentsions as $extension){
				$remote_file = $fileName.$extension;
				$local_file = $path.$fileName.$extension;
				ftp_put($connexion, $remote_file, $local_file, FTP_BINARY);
			}
			
			//rename .tmp to .cou
			$fileNameTmp = $fileName.'.tmp';
			$fileNameFtp = $fileName.'.cou';
			$rename = ftp_rename($connexion, $fileNameTmp, $fileNameFtp);
			
			ftp_close($connexion);
			
			unlink($path.$fileName.'.001');
			unlink($path.$fileName.'.002');
			unlink($path.$fileName.'.tmp');

		} catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
	}
}