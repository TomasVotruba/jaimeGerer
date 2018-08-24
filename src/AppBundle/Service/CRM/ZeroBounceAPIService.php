<?php
namespace AppBundle\Service\CRM;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;

class ZeroBounceAPIService extends ContainerAware
{

    protected $apiURL;

  	public function __construct()
  	{
   		$this->apiURL = 'https://api.zerobounce.net/v2/';
  	}

  	/**
  	 * Retourne le nombre de crédits disponibles sur ZeroBounce
  	 **/
  	public function getCreditBalance($company){

        if($company->getZeroBounceApiKey() == null){
            throw new \Exception('API Key non renseignée');
        }

  		$url = $this->apiURL.'getcredits?api_key='.$company->getZeroBounceApiKey();
  		 
  		$ch = curl_init($url);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); 
  		curl_setopt($ch, CURLOPT_TIMEOUT, 150); 
  		$response = curl_exec($ch);
  		curl_close($ch);
  		 
  		$json = json_decode($response, true);

  		if( $json['Credits'] == -1){
  			throw new \Exception('Erreur lors de l\'appel à l\'API ZeroBounce');
  		}

  		return $json['Credits'];
  	}

  	/*
  	* Vérifie si l'email $emailToValidate est un bounce via l'API ZeroBounce
  	* Return string "bounce", "valid" ou "warning"
  	**/
  	public function isBounce($contact){

        $apiKey = $contact->getCompte()->getCompany()->getZeroBounceApiKey();
        if($apiKey == null){
            throw new \Exception('API Key non renseignée');
        }

        $ip = ''; //required, but can be blank
		$url = 'https://api.zerobounce.net/v2/validate?api_key='.$apiKey.'&email='.urlencode($contact->getEmail()).'&ip_address='.$ip;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 150); 
		$response = curl_exec($ch);
		curl_close($ch);
		 
		$json = json_decode($response, true);
		if(array_key_exists('error', $json)){
  			throw new \Exception('Erreur lors de l\'appel à l\'API ZeroBounce');
  		}

		//cf https://www.zerobounce.net/docs/#status-codes-v2
        if($json["status"] == "valid"){
            return "valid";
        }

		$arr_invalidStatus = array('invalid', 'spamtrap');
		if( in_array($json["status"], $arr_invalidStatus) ){
			return "bounce";
		}

		return "warning";

  	}

}