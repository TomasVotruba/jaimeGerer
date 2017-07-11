<?php

namespace AppBundle\Service\mautic;

use AppBundle\Entity\Emailing\MauticApiKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;
use Symfony\Component\HttpFoundation\Session\Session;


class MauticApiService
{

    public $session;
    protected $apiUrl;

    public function __construct(Session $session, $apiUrl){
        $this->session = $session;
        $this->apiUrl = $apiUrl;

    }

    public function authorization(MauticApiKey $mauticApiKey){

        $publicKey = $mauticApiKey->getPublicKey();

        $secretKey = $mauticApiKey->getSecretKey();
        $callback  = 'https://logiciels.jaime-gerer.com/emailing/home';

        // ApiAuth->newAuth() will accept an array of Auth settings
        $settings = array(
            'baseUrl'          => 'https://nicomak.mautic.net',   // Base URL of the Mautic instance
            'version'          => 'OAuth2',                       // Version of the OAuth can be OAuth2 or OAuth1a. OAuth2 is the default value.
            'clientKey'        => $publicKey,                    // Client/Consumer key from Mautic
            'clientSecret'     => $secretKey,                    // Client/Consumer secret key from Mautic
            'callback'         => $callback                     // Redirect URI/Callback URI for this script
        );

        // Initiate the auth object
        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings);

        // Initiate process for obtaining an access token; this will redirect the user to the $authorizationUrl and/or
        // set the access_tokens when the user is redirected back after granting authorization

        // If the access token is expired, and a refresh token is set above, then a new access token will be requested
        try {
            if ($auth->validateAccessToken()) {

                // Obtain the access token returned; call accessTokenUpdated() to catch if the token was updated via a
                // refresh token

                // $accessTokenData will have the following keys:
                // For OAuth2: access_token, expires, token_type, refresh_token

                if ($auth->accessTokenUpdated()) {
                    $accessTokenData = $auth->getAccessTokenData();

                    //store access token data however you want
                    $this->session->set('accessTokenData', $accessTokenData);
                    $this->session->set('auth', $auth);
                    if($this->session->get('rapportId')){
                        $this->session->set('actionInit', true);
                    }
                }
            }
        } catch (\Exception $e) {
            // Do Error handling
            throw $e;
        }
    }


    public function createContact($contact){

        $api = new MauticApi();

        // Create an api context by passing in the desired context (Contacts, Forms, Pages, etc), the $auth object from above
        // and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)
        $contactApi = $api->newApi('contacts', $this->session->get('auth'), 'https://nicomak.mautic.net/api');

        try {
            $contact = $contactApi->create($contact);
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function createSegment($name, $description){
        $segmentApi = $this->initApi('segments');

        $data = array(
            'name'        => $name,
            'description' => $description,
            'isPublished' => 1
        );

        try {
            $segment = $segmentApi->create($data);

            $segmentId = $segment["list"]["id"];

        } catch (\Exception $e) {
            throw $e;
        }

        return $segmentId;
    }

    public function addContactsToSegment($segmentId, $contactsId){
        $segmentApi = $this->initApi('segments');

        $responseList = [];

        try {
            foreach($contactsId as $contactId){
                $response = $segmentApi->addContact($segmentId, $contactId);

                array_push($responseList, $response);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $responseList;

    }

    public function createContactBatch($contacts){

        $contactApi = $this->initApi('contacts');

        $contactsList = array();

        try {
            foreach($contacts as $contact){
                $response = $contactApi->create($contact);

                array_push($contactsList, $response['contact']['id']);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $contactsList;

    }


    // Create an api context by passing in the desired context (Contacts, Forms, Pages, etc), the $auth object from above
    // and the base URL to the Mautic server (i.e. http://my-mautic-server.com/api/)
    private function initApi($apiType){

        $api = new MauticApi();

        return   $api->newApi($apiType, $this->session->get('auth'), $this->apiUrl);
    }

}

