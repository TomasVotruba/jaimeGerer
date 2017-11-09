<?php

namespace AppBundle\Service\CRM;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\CRM\Devis;

class DevisService extends ContainerAware {

    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function win($devis){

        $devis->win();
        $this->em->persist($devis);
        $this->em->flush();

        return $devis;

     }

     public function lose($devis){

        $devis->lose();
        $this->em->persist($devis);
        $this->em->flush();

        return $devis;

    }

    public function setNum($devis){
        
        $settingsRepository = $this->em->getRepository('AppBundle:Settings');
        $settingsNum = $settingsRepository->findOneBy(array('company' => $devis->getuserCreation()->getCompany(), 'module' => 'CRM', 'parametre' => 'NUMERO_DEVIS'));
        $currentNum = $settingsNum->getValeur();

        $prefixe = date('Y').'-';
        if($currentNum < 10){
            $prefixe.='00';
        } else if ($currentNum < 100){
            $prefixe.='0';
        }
        $devis->setNum($prefixe.$currentNum);
        $currentNum++;
        $settingsNum->setValeur($currentNum);
        $this->em->persist($settingsNum);

        return $devis;
    }

    public function createFromOpportunite($devis, $opportunite){

        $devis->setCompte($opportunite->getCompte());
        $devis->setContact($opportunite->getContact());
        $devis->setDateCreation($opportunite->getDateCreation());
        $devis->setUserCreation($opportunite->getuserCreation());
        $devis->setObjet($opportunite->getNom());
        $devis->setUserGestion($opportunite->getUserGestion());
        $devis->setAnalytique($opportunite->getAnalytique());

        return $devis;
    }
}
