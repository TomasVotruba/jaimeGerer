<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;


class TableauTVAService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
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

			$arr_soumis = array();
			$arr_non_soumis = array();

			$arr_rapprochements = $rapprochementsRepo->findForPeriodeEncaissement(
				$company,
				$arr_periode['mois'],
				$arr_periode['annee']
			);

			//ENTREE
			$arr_soumis['entreeHT'] = 0;
			$arr_soumis['entreeTVA'] = 0;
			$arr_soumis['entreeTTC'] = 0;
			$arr_soumis['taxe_percent'] = array(
					'55' => 0,
					'100' => 0,
					'200' => 0,
					'other' => 0
			);

			$arr_non_soumis['entreePrixNet'] = 0;

			$arr_factures = array();
			if($settingsEntree->getValeur() == 'ENCAISSEMENTS'){
				// ENCAISSEMENTS = au rapprochement
				foreach($arr_rapprochements as $rapprochement){

					if($rapprochement->getFacture()){
						//non soumis à TVA
						if($rapprochement->getFacture()->getAnalytique()->getNoTVA()){
							$arr_non_soumis['entreePrixNet']+= $rapprochement->getFacture()->getTotalHT();
						} else {
							//soumis à TVA
							$arr_soumis['entreeTTC']+= $rapprochement->getFacture()->getTotalTTC();
							$arr_soumis['entreeHT']+= $rapprochement->getFacture()->getTotalHT();
							$arr_soumis['entreeTVA']+= $rapprochement->getFacture()->getTaxe();
							$taxePercent = $rapprochement->getFacture()->getTaxePercent()*1000;
							if($taxePercent != 0){
								if(array_key_exists(intval($taxePercent), $arr_soumis['taxe_percent'])){
									$arr_soumis['taxe_percent'][$taxePercent]+=$rapprochement->getFacture()->getTaxe();
								} else {
									$arr_soumis['taxe_percent']['other']+=$rapprochement->getFacture()->getTaxe();
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
										$arr_non_soumis['entreePrixNet']+= $piece->getFacture()->getTotalHT();
									} else {
										//soumis à TVA
										$arr_soumis['entreeTTC']+= $piece->getFacture()->getTotalTTC();
										$arr_soumis['entreeHT']+= $piece->getFacture()->getTotalHT();
										$arr_soumis['entreeTVA']+= $piece->getFacture()->getTaxe();
										$taxePercent = $piece->getFacture()->getTaxePercent()*1000;
										if($taxePercent != 0){
											if(array_key_exists(intval($taxePercent), $arr_soumis['taxe_percent'])){
												$arr_soumis['taxe_percent'][$taxePercent]+=$piece->getFacture()->getTaxe();
											} else {
												$arr_soumis['taxe_percent']['other']+=$piece->getFacture()->getTaxe();
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
			$arr_soumis['sortieHT'] = 0;
			$arr_soumis['sortieTVA'] = 0;
			$arr_soumis['sortieTTC'] = 0;

			$arr_non_soumis['sortiePrixNet'] = 0;

			$arr_depenses = array();
			if($settingsSortie->getValeur() == 'ENCAISSEMENTS'){
				// ENCAISSEMENTS = au rapprochement
				foreach($arr_rapprochements as $rapprochement){
					if($rapprochement->getDepense()){
						//non soumis à TVA

						if($rapprochement->getDepense()->getAnalytique()->getNoTVA()){
							$arr_non_soumis['sortiePrixNet']+= $rapprochement->getDepense()->getTotalHT();
						} else {
							//soumis à TVA
							$arr_soumis['sortieTTC']+= $rapprochement->getDepense()->getTotalTTC();
							$arr_soumis['sortieHT']+= $rapprochement->getDepense()->getTotalHT();
							$arr_soumis['sortieTVA']+= $rapprochement->getDepense()->getTotalTVA();

						}
					}
				}
			} else {
				//ENGAGEMENTS = à la création
				$arr_depenses = $depenseRepo->findForPeriodeEngagement($company, $arr_periode['mois'], $arr_periode['annee']);
				foreach($arr_depenses as $depense){
					//non soumis à TVA
					if($depense->getAnalytique()->getNoTVA()){
						$arr_non_soumis['sortiePrixNet']+= $depense->getTotalHT();
					} else {
						//soumis à TVA
						$arr_soumis['sortieTTC']+= $depense->getTotalTTC();
						$arr_soumis['sortieHT']+= $depense->getTotalHT();
						$arr_soumis['sortieTVA']+= $depense->getTotalTVA();
					}
				}
			}

			$arr_periode['sortie_soumis'] = $arr_soumis;
			$arr_periode['sortie_non_soumis'] = $arr_non_soumis;

			$balance = $arr_soumis['sortieTVA']-$arr_soumis['entreeTVA'];
			$arr_periode['balance'] = $balance;

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
      //non soumis à TVA
      if($facture->getAnalytique()->getNoTVA()){
        $arr_non_soumis['entreePrixNet']+= $facture->getTotalHT();
      } else {
        //soumis à TVA
        $arr_soumis['entreeTTC']+= $facture->getTotalTTC();
        $arr_soumis['entreeHT']+= $facture->getTotalHT();
        $arr_soumis['entreeTVA']+= $facture->getTaxe();
        $taxePercent = $facture->getTaxePercent()*1000;
        if($taxePercent != 0){
          if(array_key_exists(intval($taxePercent), $arr_soumis['taxe_percent'])){
            $arr_soumis['taxe_percent'][$taxePercent]+=$facture->getTaxe();
          } else {
            $arr_soumis['taxe_percent']['other']+=$facture->getTaxe();
          }
        }
      }
    }

  }

}
