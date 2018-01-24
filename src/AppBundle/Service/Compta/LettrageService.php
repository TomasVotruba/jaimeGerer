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

        if($annee == null){
            $annee = date('Y');
        }

  		$arr_max = array();

        $arr_lettrage_vente = $journalVenteRepo->findAllLettrage($compteComptable, $annee);
        rsort($arr_lettrage_vente);
        if(count($arr_lettrage_vente) > 0){
            $arr_max['vente'] = $arr_lettrage_vente[0][1];
        }

        $arr_lettrage_achat = $journalAchatRepo->findAllLettrage($compteComptable, $annee);
        rsort($arr_lettrage_achat);
        if(count($arr_lettrage_achat) > 0){
            $arr_max['achat'] = $arr_lettrage_achat[0][1];
        }

        $arr_lettrage_banque = $journalBanqueRepo->findAllLettrage($compteComptable, $annee);
        rsort($arr_lettrage_banque);
        if(count($arr_lettrage_banque) > 0){
            $arr_max['banque'] = $arr_lettrage_banque[0][1];
        }
        
        

  		arsort($arr_max); //Trie un tableau en ordre inverse et conserve l'association des index
        dump($arr_max);
  		$maxLettrage = reset($arr_max); // replace le pointeur de tableau array au premier élément et retourne la valeur du premier élément
  		
  		if($maxLettrage === 0 or $maxLettrage === null or $maxLettrage === ""){
  			$maxLettrage = 'A';
  		} else {
  			$maxLettrage++;
  		}
        dump($maxLettrage);
  		return $maxLettrage;
  	}


}
