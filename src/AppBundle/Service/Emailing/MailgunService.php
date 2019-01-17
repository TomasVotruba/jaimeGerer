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
    public function sendViaAPI($campagne){

        $mgClient = new \Mailgun\Mailgun($this->apiKey);
       
        $result = $mgClient->sendMessage($this->domain, array(
            'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
            'to'      => $campagne->getDestinataires(),
            'subject' => $campagne->getObjet(),
           // 'text' => $campagne->getObjet(),
            'html'    => $campagne->getHtml(),
            'recipient-variables' => '{}',
            'v:campagne-id'   =>  $campagne->getId(),
            'v:company-id'   =>  $campagne->getUserCreation()->getCompany()->getId()
        ));

        return $result;
    }

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

                        case 'opened':
                            $campagneContact->setOpen(true);
                            $campagneContact->setOpenDate(new \DateTime(date('Y-m-d', $timestamp)));

                        case 'clicked':
                            $campagneContact->setClick(true);
                            $campagneContact->setClickDate(new \DateTime(date('Y-m-d', $timestamp)));

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