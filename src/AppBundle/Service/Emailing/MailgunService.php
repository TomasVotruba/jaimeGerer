<?php

namespace AppBundle\Service\Emailing;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\CRM\Campagne;
use AppBundle\Entity\CRM\CampagneContact;

use Mailgun\Mailgun;

class MailgunService extends ContainerAware {

    private $apiKey;
    private $domain;
    private $em;


    public function __construct($apiKey, $domain, $em)
    {
        $this->apiKey = $apiKey;
        $this->domain = $domain;
        $this->em = $em;
    }

    /**
     * Send $campagne via MailGun API
     **/ 
    public function sendTestViaAPI($campagne, $to){

        $mgClient = Mailgun::create($this->apiKey, 'https://api.eu.mailgun.net');
        $result = $mgClient->messages()->send($this->domain, [
            'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
            'to'      => $to,
            'subject' => '[TEST] '.$campagne->getObjet(),
            'html'    => $campagne->getHtml(),
        ]);
     
        return $result;
    }

    /**
     * Send $campagne via MailGun API
     **/ 
    public function sendCampagneViaAPI($campagne){

        $mgClient = Mailgun::create($this->apiKey, 'https://api.eu.mailgun.net');
        $arr_destinataires = $campagne->getDestinataires();
       
        $results = array();

        //send by 500 as MailGun limit is 1000 at the same time
        $chunks = array_chunk($arr_destinataires,500,true);
        foreach($chunks as $chunk){

            $params = [
                'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
                'to'      => $chunk,
                'subject' => $campagne->getObjet(),
                'html'    => $campagne->getHtml(),
                'recipient-variables' => '{}',
                'v:campagne-id'   =>  $campagne->getId(),
                'v:company-id'   =>  $campagne->getUserCreation()->getCompany()->getId()
            ];

            if($campagne->isScheduled()){
                $params['o:deliverytime'] = $campagne->getDateEnvoi()->getTimestamp();
            }

            $results[] = $mgClient->messages()->send($this->domain, $params);
        }
       
        return $results;
    }

    // /**
    //  * Add unsubsribe link at the bottom of $campagne
    //  **/ 
    // public function ajouterLienDesinscription($campagne){

    //     $unsubscribeLink = '<p style="text-align: center; margin-top: 20px;"><a href="#">Désinscription</a></p>';
    //     $html = $campagne->getHtml();
    //     $campagne->setHtml($html.$unsubscribeLink);

    //     $this->em->persist($campagne);
    //     $this->em->flush();
    // }

    /**
     * Check request signature when receiving a call from a Mailgun webhook
     **/
    public function checkWebhookSignature($token, $timestamp, $signature){
        
        /*
            From Mailgun docs : https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
            To verify the webhook is originating from Mailgun you need to:
                - Concatenate timestamp and token values.
                - Encode the resulting string with the HMAC algorithm (using your API Key as a key and SHA256 digest mode).
                - Compare the resulting hexdigest to the signature.
                - Optionally, you can check if the timestamp is not too far from the current time.
            
        */

        //check if the timestamp is fresh
        if (abs(time() - $timestamp) > 15) {
            return false;
        }

        //check signature
        return hash_hmac('sha256', $timestamp.$token, $this->apiKey) === $signature;
    }

    public function saveWebhookEvent($eventData){

        $contactEmail = $eventData['recipient'];
        $campagneId = $eventData['user-variables']['campagne-id'];
        $companyId = $eventData['user-variables']['company-id'];
        $timestamp = $eventData['timestamp'];

        $event = $eventData['event'];

        if(!$contactEmail || !$campagneId || !$companyId){
            return false;
        }

        try{
            $contactRepository = $this->em->getRepository('AppBundle:CRM\Contact');
            $campagneContactRepository = $this->em->getRepository('AppBundle:Emailing\CampagneContact');

            $contact = $contactRepository->findByEmailAndCompany($contactEmail, $companyId);

            if($contact){

                $campagneContact = $campagneContactRepository->findOneBy(array(
                    'contact' => $contact,
                    'campagne' => $campagneId
                ));

                if($campagneContact){

                    switch($event){

                        case 'delivered':
                            $campagneContact->setDelivered(true);
                            $campagneContact->setDeliveredDate(new \DateTime(date('Y-m-d', $timestamp)));

                            $campagne = $campagneContact->getCampagne();
                            if($campagne->isDelivering()){
                                $campagne->setEtat('SENT');
                                $em->persist($campagne);
                            }

                        case 'opened':
                            $campagneContact->setOpen(true);
                            $campagneContact->setOpenDate(new \DateTime(date('Y-m-d', $timestamp)));

                        case 'clicked':
                            $campagneContact->setClick(true);
                            $campagneContact->setClickDate(new \DateTime(date('Y-m-d', $timestamp)));


                        case 'failed':
                            $campagneContact->setBounce(true);
                            $campagneContact->setBounceDate(new \DateTime(date('Y-m-d', $timestamp)));
                            $campagneContact->getContact()->setBounce('BOUNCE');
                            $this->em->persist($campagneContact->getContact());

                        case 'unsubscribed':
                            $campagneContact->setUnsubscribed(true);
                            $campagneContact->setUnsubscribedDate(new \DateTime(date('Y-m-d', $timestamp)));
                            $campagneContact->getContact()->setRejetEmail(true);
                            $campagneContact->getContact()->setRejetNewsletter(true);
                            $this->em->persist($campagneContact->getContact());

                    }
                    
                    $this->em->persist($campagneContact);
                    $this->em->flush();
                } else {
                    return false;
                }

            } else {
                return false;
            }

        } catch(\Exception $e){
           return false;
        }

        return true;

    }   

}