<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Compta\Depense;

class LettrageService extends ContainerAware {

  	protected $em;

  	public function __construct(\Doctrine\ORM\EntityManager $em)
  	{
   		$this->em = $em;
  	}

  	public function findNextNum($compteComptable, $annee = null){

  		$journalVenteRepo = $this->em->getRepository('AppBundle:Compta\JournalVente');
  		$journalAchatRepo = $this->em->getRepository('AppBundle:Compta\JournalAchat');
  		$journalBanqueRepo = $this->em->getRepository('AppBundle:Compta\JournalBanque');

        if($annee = null){
            $annee = date('Y');
        }

  		$arr_max = array();
  		$arr_max['vente'] = $journalVenteRepo->findMaxLettrage($compteComptable, $annee)['max_lettrage'];
  		$arr_max['achat'] = $journalAchatRepo->findMaxLettrage($compteComptable, $annee)['max_lettrage'];
  		$arr_max['banque'] = $journalBanqueRepo->findMaxLettrage($compteComptable, $annee)['max_lettrage'];

  		arsort($arr_max); //Trie un tableau en ordre inverse et conserve l'association des index
  		$maxLettrage = reset($arr_max); // replace le pointeur de tableau array au premier élément et retourne la valeur du premier élément
  		
  		if($maxLettrage === 0 or $maxLettrage === null or $maxLettrage === ""){
  			$maxLettrage = 'A';
  		} else {
  			$maxLettrage++;
  		}
  		
  		return $maxLettrage;
  	}


}
