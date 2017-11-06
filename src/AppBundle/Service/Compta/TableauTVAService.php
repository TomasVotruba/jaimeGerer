<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class TableauTVAService extends ContainerAware {

  protected $em;
  protected $utilsService;

  public function __construct(\Doctrine\ORM\EntityManager $em, $utilsService)
  {
    $this->em = $em;
    $this->utilsService = $utilsService;
  }

  public function creerTableauTVA($company, $year){
		/* encaissements ou engagements ?
		* ENCAISSEMENTS = au rapprochement
		* ENGAGEMENTS = à la création
		*/
		$settingsRepo = $this->em->getRepository('AppBundle:Settings');
		// $settingsActivationRepo = $this->em->getRepository('AppBundle:SettingsActivationOutil');
		$documentPrixRepo = $this->em->getRepository('AppBundle:CRM\DocumentPrix');
		$depenseRepo = $this->em->getRepository('AppBundle:Compta\Depense');
		$rapprochementsRepo = $this->em->getRepository('AppBundle:Compta\Rapprochement');

		$settingsEntree = $settingsRepo->findOneBy(array(
				'company' => $company,
				'module' => 'COMPTA',
				'parametre' => 'TVA_ENTREE'
		));
		$settingsSortie = $settingsRepo->findOneBy(array(
				'company' => $company,
				'module' => 'COMPTA',
				'parametre' => 'TVA_SORTIE'
		));

		$arr_tva = array();
   		$start = new \DateTime($year.'-01-01');
    	$end = new \DateTime($year.'-12-31');
		$interval = \DateInterval::createFromDateString('1 month');
		$periode  = new \DatePeriod($start, $interval, $end);

		foreach ($periode as $dt) {

			$arr_periode = array();

			$arr_periode['mois'] =  $dt->format("m");
			$arr_periode['annee'] = $dt->format("y");

			$arr_soumis = array(
				'france' => array(),
				'intra' => array(),
				'extra' => array()
			);
			$arr_non_soumis = array(
				'france' => array(),
				'intra' => array(),
				'extra' => array()
			);

			$arr_rapprochements = $rapprochementsRepo->findForPeriodeEncaissement(
				$company,
				$arr_periode['mois'],
				$arr_periode['annee']
			);

			//ENTREE
			foreach($arr_soumis as $type => $arr){
				$arr_soumis[$type]['entreeHT'] = 0;
				$arr_soumis[$type]['entreeTVA'] = 0;
				$arr_soumis[$type]['entreeTTC'] = 0;
				$arr_soumis[$type]['taxe_percent'] = array(
						'55' => 0,
						'100' => 0,
						'200' => 0,
						'other' => 0
				);
			}

			foreach($arr_non_soumis as $type => $arr){
				$arr_non_soumis[$type]['entreeHT'] = 0;
				$arr_non_soumis[$type]['entreeTTC'] = 0;
			}

			$arr_factures = array();
			if($settingsEntree->getValeur() == 'ENCAISSEMENTS'){
				// ENCAISSEMENTS = au rapprochement
				foreach($arr_rapprochements as $rapprochement){

					if($rapprochement->getFacture()){

						$type = 'extra';
						$facture = $rapprochement->getFacture();
						if(strtolower($facture->getPays()) == "france" || $facture->getPays() == null || $facture->getPays() == ""){
							$type = 'france';
						} else if($this->inUE($facture->getPays())){
							$type = 'intra';
						} 

						//non soumis à TVA
						if($rapprochement->getFacture()->getAnalytique()->getNoTVA()){
							$arr_non_soumis[$type]['entreeHT']+= $rapprochement->getFacture()->getTotalHT();
							$arr_non_soumis[$type]['entreeTTC']+= $rapprochement->getFacture()->getTotalTTC();
						} else {
							//soumis à TVA
							$arr_soumis[$type]['entreeTTC']+= $rapprochement->getFacture()->getTotalTTC();
							$arr_soumis[$type]['entreeHT']+= $rapprochement->getFacture()->getTotalHT();
							$arr_soumis[$type]['entreeTVA']+= $rapprochement->getFacture()->getTaxe();
							$taxePercent = $rapprochement->getFacture()->getTaxePercent()*1000;
							if($taxePercent != 0){
								if(array_key_exists(intval($taxePercent), $arr_soumis[$type]['taxe_percent'])){
									$arr_soumis[$type]['taxe_percent'][$taxePercent]+=$rapprochement->getFacture()->getTaxe();
								} else {
									$arr_soumis[$type]['taxe_percent']['other']+=$rapprochement->getFacture()->getTaxe();
								}
							}
						}

					} elseif($rapprochement->getRemiseCheque()){
						$arr_cheques = $rapprochement->getRemiseCheque()->getCheques();
						foreach($arr_cheques as $cheque){
							foreach($cheque->getPieces() as $piece){
								if($piece->getFacture()){
									$taxePercent = $piece->getFacture()->getTaxePercent()*1000;
									//non soumis à TVA
									if($piece->getFacture()->getAnalytique()->getNoTVA()){
										$arr_non_soumis[$type]['entreeHT']+= $piece->getFacture()->getTotalHT();
										$arr_non_soumis[$type]['entreeTTC']+= $piece->getFacture()->getTotalTTC();
									} else {
										//soumis à TVA
										$arr_soumis[$type]['entreeTTC']+= $piece->getFacture()->getTotalTTC();
										$arr_soumis[$type]['entreeHT']+= $piece->getFacture()->getTotalHT();
										$arr_soumis[$type]['entreeTVA']+= $piece->getFacture()->getTaxe();
										$taxePercent = $piece->getFacture()->getTaxePercent()*1000;
										if($taxePercent != 0){
											if(array_key_exists(intval($taxePercent), $arr_soumis[$type]['taxe_percent'])){
												$arr_soumis[$type]['taxe_percent'][$taxePercent]+=$piece->getFacture()->getTaxe();
											} else {
												$arr_soumis[$type]['taxe_percent']['other']+=$piece->getFacture()->getTaxe();
											}
										}

									}
								}
							}

						}
					}
				}
			} else {
        		$this->entreesEngagement($company, $arr_periode, $arr_soumis, $arr_non_soumis);
			}

			$arr_periode['entree_soumis'] = $arr_soumis;
			$arr_periode['entree_non_soumis'] = $arr_non_soumis;

			//SORTIE
			foreach($arr_soumis as $type => $arr){
				$arr_soumis[$type]['sortieHT'] = 0;
				$arr_soumis[$type]['sortieTVA'] = 0;
				$arr_soumis[$type]['sortieTTC'] = 0;
			}

			foreach($arr_non_soumis as $type => $arr){
				$arr_non_soumis[$type]['sortieHT'] = 0;
				$arr_non_soumis[$type]['sortieTTC'] = 0;
			}

	

			$arr_depenses = array();
			if($settingsSortie->getValeur() == 'ENCAISSEMENTS'){
				// ENCAISSEMENTS = au rapprochement
				foreach($arr_rapprochements as $rapprochement){
					if($rapprochement->getDepense()){

						$type = 'extra';
						$depense = $rapprochement->getDepense();
						if(strtolower($depense->getCompte()->getPays()) == "france" || $depense->getCompte()->getPays() == null || $depense->getCompte()->getPays() == ""){
							$type = 'france';
						} else if($this->inUE($depense->getCompte()->getPays())){
							$type = 'intra';
						} 


						//non soumis à TVA
						if($rapprochement->getDepense()->getAnalytique()->getNoTVA()){
							$arr_non_soumis[$type]['sortieHT']+= $rapprochement->getDepense()->getTotalHT();
							$arr_non_soumis[$type]['sortieTTC']+= $rapprochement->getDepense()->getTotalTTC();
						} else {
							//soumis à TVA
							$arr_soumis[$type]['sortieTTC']+= $rapprochement->getDepense()->getTotalTTC();
							$arr_soumis[$type]['sortieHT']+= $rapprochement->getDepense()->getTotalHT();
							$arr_soumis[$type]['sortieTVA']+= $rapprochement->getDepense()->getTotalTVA();

						}
					}
				}
			} else {
				//ENGAGEMENTS = à la création
				$arr_depenses = $depenseRepo->findForPeriodeEngagement($company, $arr_periode['mois'], $arr_periode['annee']);
				foreach($arr_depenses as $depense){

					$type = 'extra';
					if(strtolower($depense->getCompte()->getPays()) == "france" || $depense->getCompte()->getPays() == null || $depense->getCompte()->getPays() == ""){
						$type = 'france';
					} else if($this->inUE($depense->getCompte()->getPays())){
						$type = 'intra';
					} 

					//non soumis à TVA
					if($depense->getAnalytique()->getNoTVA()){
						$arr_non_soumis[$type]['sortieHT']+= $depense->getTotalHT();
						$arr_non_soumis[$type]['sortieTTC']+= $depense->getTotalTTC();
					} else {
						//soumis à TVA
						$arr_soumis[$type]['sortieTTC']+= $depense->getTotalTTC();
						$arr_soumis[$type]['sortieHT']+= $depense->getTotalHT();
						$arr_soumis[$type]['sortieTVA']+= $depense->getTotalTVA();
					}
				}
			}

			$arr_periode['sortie_soumis'] = $arr_soumis;
			$arr_periode['sortie_non_soumis'] = $arr_non_soumis;

			$arr_types = array('france', 'intra', 'extra');
			foreach($arr_types as $type){
				$balance = $arr_soumis[$type]['sortieTVA']-$arr_soumis[$type]['entreeTVA'];
				$arr_periode['balance'][$type] = $balance;
			}
			
			$arr_tva[] = $arr_periode;
		}
		return $arr_tva;
	}

  private function entreesEngagement($company, $arr_periode, $arr_soumis, $arr_non_smoumis){

    $documentPrixRepo = $this->em->getRepository('AppBundle:CRM\DocumentPrix');

    //ENGAGEMENTS = à la création
    $arr_factures = $documentPrixRepo->findForPeriodeEngagement(
      $company,
      $arr_periode['mois'],
      $arr_periode['annee']
    );

    foreach($arr_factures as $facture){

		$type = 'extra';
		$facture = $rapprochement->getFacture();
		if(strtolower($facture->getPays()) == "france" || $facture->getPays() == null || $facture->getPays() == ""){
			$type = 'france';
		} else if($this->inUE($facture->getPays())){
			$type = 'intra';
		} 

      //non soumis à TVA
      if($facture->getAnalytique()->getNoTVA()){
        $arr_non_soumis['entreeHT']+= $facture->getTotalHT();
        $arr_non_soumis['entreeTTC']+= $facture->getTotalTTC();
      } else {
        //soumis à TVA
        $arr_soumis[$type]['entreeTTC']+= $facture->getTotalTTC();
        $arr_soumis[$type]['entreeHT']+= $facture->getTotalHT();
        $arr_soumis[$type]['entreeTVA']+= $facture->getTaxe();
        $taxePercent = $facture->getTaxePercent()*1000;
        if($taxePercent != 0){
          if(array_key_exists(intval($taxePercent), $arr_soumis[$type]['taxe_percent'])){
            $arr_soumis[$type]['taxe_percent'][$taxePercent]+=$facture->getTaxe();
          } else {
            $arr_soumis[$type]['taxe_percent']['other']+=$facture->getTaxe();
          }
        }
      }
    }

  }

 	private function inUE($country){
 		$arr_ue = array(
 			'Allemagne',
 			'Autriche',
 			'Belgique',
 			'Bulgarie',
 			'Chypre',
 			'Croatie',
 			'Danemark',
 			'Espagne',
 			'Estonie',
 			'Finlande',
 			'Grèce',
 			'Hongrie',
 			'Irlande',
 			'Italie',
 			'Lettonie',
 			'Lituanie',
 			'Luxembourg',
 			'Malte',
 			'Pays-Bas',
 			'Pologne',
 			'Portugal',
 			'République tchèque',
 			'Roumanie',
 			'Royaume-Uni',
 			'Slovaquie',
 			'Slovénie',
 			'Suède'
 		);

 		$arr_ue_simplified = array();
 		foreach($arr_ue as $ueCountry){
 			$arr_ue_simplified[] = $this->utilsService->removeSpecialChars($ueCountry);
 		}

 		$countrySimplified = $this->utilsService->removeSpecialChars($country);

 		if(in_array($countrySimplified, $arr_ue_simplified)){
 			return true;
 		}
 		return false;
  	}

}
