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

        $all_lettrage_vente = $journalVenteRepo->findAllLettrage($compteComptable, $annee);
        $arr_lettrage_vente = array();
         foreach($all_lettrage_vente as $arr_lettrage){
            $arr_lettrage_vente[] = $arr_lettrage[1];
        }
        if(count($arr_lettrage_vente) > 0){
            usort($arr_lettrage_vente,array($this, "sortLetters"));
            $arr_max['vente'] = $arr_lettrage_vente[0];
        }

        $all_lettrage_achat = $journalAchatRepo->findAllLettrage($compteComptable, $annee);
        $arr_lettrage_achat = array();
        foreach($all_lettrage_achat as $arr_lettrage){
            $arr_lettrage_achat[] = $arr_lettrage[1];
        }
        if(count($arr_lettrage_achat) > 0){
            usort($arr_lettrage_achat,array($this, "sortLetters"));
            $arr_max['achat'] = $arr_lettrage_achat[0];
        }

        $all_lettrage_banque = $journalBanqueRepo->findAllLettrage($compteComptable, $annee);
        $arr_lettrage_banque = array();
        foreach($all_lettrage_banque as $arr_lettrage){
            $arr_lettrage_banque[] = $arr_lettrage[1];
        }
        if(count($arr_lettrage_banque) > 0){
            usort($arr_lettrage_banque,array($this, "sortLetters"));
            $arr_max['banque'] = $arr_lettrage_banque[0];
        }

  		arsort($arr_max); //Trie un tableau en ordre inverse et conserve l'association des index
  		$maxLettrage = reset($arr_max); // replace le pointeur de tableau array au premier élément et retourne la valeur du premier élément
  		
  		if($maxLettrage === 0 or $maxLettrage === null or $maxLettrage === ""){
  			$maxLettrage = 'A';
  		} else {
  			$maxLettrage++;
  		}
  		return $maxLettrage;
  	}


    public function sortLetters($a, $b){
        if(strlen($b) == strlen($a)){
            return $b > $a;
        }
        return strlen($b) - strlen($a);
    }


}
