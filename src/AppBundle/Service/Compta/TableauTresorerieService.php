<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\UtilsService;
use AppBundle\Entity\Compta\PrevTreso;

class TableauTresorerieService extends ContainerAware {

  protected $em;
  protected $utilsService;
  protected $arr_postes_entree;
  protected $arr_postes_sortie;

  public function __construct(\Doctrine\ORM\EntityManager $em, UtilsService $utilsService)
  {
    $this->em = $em;
    $this->utilsService = $utilsService;
    $this->arr_postes_entree = array(
      'FACTURES',
      'AVOIRS_FOURNISSEURS',
      'REMBOURSEMENT_TAXES',
      'DECAISSEMENTS_EMPRUNTS',
      'DECAISSEMENTS_SUBVENTIONS',
      'VIREMENTS_ENTREE',
      'COMPTES_COURANTS_ASSOCIES_ENTREE',
      'AUTRES_ENTREES'
    );
    $this->arr_postes_sortie = array(
      'DEPENSES',
      'AVOIRS_CLIENTS',
      'TAXES',
      'SALAIRES',
      'CHARGES',
      'REMBOURSEMENTS_EMPRUNTS',
      'VIREMENTS_SORTIE',
      'COMPTES_COURANTS_ASSOCIES_SORTIE',
      'AUTRES_SORTIES'
    );
  }

  public function creerTableauTresorerie($compteBancaireId, $company, $year){

		if($compteBancaireId != 'all'){

      $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
      $compteBancaire = $compteBancaireRepo->find($compteBancaireId);
      $tableauPrevisonnel = $this->calculTresoreriePrevisonnelle($compteBancaire, $year);
      $tableauAccurate = $this->calculTresorerieAccurate($compteBancaire, $year);

		} else {

		  $compteBancaire = null;
	    $tableauPrevisonnel = $this->creerTableauTresoreriePrevisionnelTousComptesBancaires($company, $year);
	    $tableauAccurate = $this->creerTableauTresorerieAccurateTousComptesBancaires($company, $year);
		}

    return array(
      'compteBancaire' => $compteBancaire,
      'arr_prev' => $tableauPrevisonnel,
      'arr_accurate' => $tableauAccurate,
    );
  }

  private function creerTableauTresoreriePrevisionnelTousComptesBancaires($company, $year){

    $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
    $arr_comptesBancaires = $compteBancaireRepo->findByCompany($company);

    $arr_prev = array();

    foreach($arr_comptesBancaires as $compteBancaire){
    	$prev = $this->calculTresoreriePrevisonnelle($compteBancaire, $year);

    	foreach($prev as $mois => $arr){

    		foreach($arr as $poste => $montant){

    			if(!array_key_exists($mois, $arr_prev)){
    				$arr_prev[$mois] = array();
    			}

    			if(array_key_exists($poste, $arr_prev[$mois])){
    				$arr_prev[$mois][$poste]+=$montant;
    			} else {
    				$arr_prev[$mois][$poste]=$montant;
    			}
    		}
    	}
    }


    return $arr_prev;

  }

  private function creerTableauTresorerieAccurateTousComptesBancaires($company, $year){

    $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
    $arr_comptesBancaires = $compteBancaireRepo->findByCompany($company);

    $arr_accurate = array();

    foreach($arr_comptesBancaires as $compteBancaire){
    	$acc = $this->calculTresorerieAccurate($compteBancaire, $year);

    	foreach($acc as $mois => $arr){

    		foreach($arr as $poste => $montant){

    			if(!array_key_exists($mois, $arr_accurate)){
    				$arr_accurate[$mois] = array();
    			}

    			if(array_key_exists($poste, $arr_accurate[$mois])){
    				$arr_accurate[$mois][$poste]+=$montant;
    			} else {
    				$arr_accurate[$mois][$poste]=$montant;
    			}
    		}
    	}

    }

    return $arr_accurate;

  }

  public function calculTresorerieAccurate($compteBancaire, $year){

    $rapprochementsRepo = $this->em->getRepository('AppBundle:Compta\Rapprochement');

    $soldeRepo = $this->em->getRepository('AppBundle:Compta\SoldeCompteBancaire');
    $solde_debut_annee = $soldeRepo->findOneBy(array(
      'compteBancaire' => $compteBancaire,
      'date' => $this->utilsService->getFirstDayOfYear($year)
    ));

    if($solde_debut_annee == null){
      throw new \Exception("Vous n'avez pas indiqué le solde du compte bancaire "+$compteBancaire->getNom()+" au 1er janvier "+$year);
    }

    $arr_accurate = array();
    $solde_depart = 0;
    for($mois=1; $mois <= 12; $mois++){

      $arr_rapprochements = $rapprochementsRepo->findTresoForPeriode($compteBancaire, $mois, $year);

      //entree
      $total_factures = 0;
      $total_avoirs_fournisseurs = 0;
      $total_remboursement_taxes = 0;
      $total_decaissements_emprunts = 0;
      $total_decaissements_subventions = 0;
      $total_virements_entree = 0;
      $total_comptes_courants_associes_entree = 0;
      $total_autres_entrees = 0;

      //sortie
      $total_depenses = 0;
      $total_avoirs_clients = 0;
      $total_taxes = 0;
      $total_salaires = 0;
      $total_charges = 0;
      $total_remboursements_emprunts = 0;
      $total_virements_sortie = 0;
      $total_comptes_courants_associes_sortie = 0;
      $total_autres_sorties = 0;

      foreach($arr_rapprochements as $rapprochement){

        if($rapprochement->getFacture() || $rapprochement->getRemiseCheque()){

          //FACTURES
          $total_factures+=$rapprochement->getMouvementBancaire()->getMontant();

        } else if($rapprochement->getAvoir()){

          //AVOIRS
          if($rapprochement->getAvoir()->getType() == 'FOURNISSEUR'){
            $total_avoirs_fournisseurs+=$rapprochement->getMouvementBancaire()->getMontant();
          } else {
            $total_avoirs_clients+=$rapprochement->getMouvementBancaire()->getMontant();
          }

        } else if($rapprochement->getDepense()){

          //DEPENSES
          $total_depenses+=$rapprochement->getMouvementBancaire()->getMontant();

        } else if($rapprochement->getAffectationDiverse() && $rapprochement->getMouvementBancaire()->getMontant() > 0){

          if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '44') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '7717')){
            //REMBOURSEMENTS TAXES
            $total_remboursement_taxes+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '1') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '51') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '52') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '53')){
            //DECAISSEMENTS EMPRUNTS
            $total_decaissements_emprunts+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '74')){
            //DECAISSEMENTS SUBVENTIONS

            $total_decaissements_subventions+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '58')){
            //VIREMENTS DE COMPTE A COMPTE (ENTREE)
            $total_virements_entree+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '455')){
            //COMPTES COURANTS ASSOCIES (ENTREE)
            $total_comptes_courants_associes_entree+=$rapprochement->getMouvementBancaire()->getMontant();

          } else {
            //AUTRES (ENTREE)
            $total_autres_entrees+=$rapprochement->getMouvementBancaire()->getMontant();

          }

        } else if($rapprochement->getAffectationDiverse() && $rapprochement->getMouvementBancaire()->getMontant() < 0){

          if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '44') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '63')){
            //TAXES
            $total_taxes+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '641') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '644') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '644' )){
            //SALAIRES
            $total_salaires+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '645') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '646') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '647') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '648')){
            //CHARGES
            $total_charges+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '1') || $this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '661') ){
            //REMBOURSEMENTS EMPRUNTS
            $total_remboursements_emprunts+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '58')){
            //VIREMENTS DE COMPTE A COMPTE (SORTIES)
            $total_virements_sortie+=$rapprochement->getMouvementBancaire()->getMontant();

          } else if($this->utilsService->startsWith($rapprochement->getAffectationDiverse()->getCompteComptable(), '455')){
            //COMPTES COURANTS ASSOCIES (SORTIE)
            $total_comptes_courants_associes_sortie+=$rapprochement->getMouvementBancaire()->getMontant();

          } else {
            //AUTRES (SORTIE)
            $total_autres_sorties+=$rapprochement->getMouvementBancaire()->getMontant();
          }

        }

      } // end foreach($arr_rapprochements as $rapprochement){

      $total_entree = $total_factures
                      + $total_avoirs_fournisseurs
                      + $total_remboursement_taxes
                      + $total_decaissements_emprunts
                      + $total_decaissements_subventions
                      + $total_virements_entree
                      + $total_comptes_courants_associes_entree
                      + $total_autres_entrees;

      $total_sortie = $total_depenses
                      + $total_avoirs_clients
                      + $total_taxes
                      + $total_salaires
                      + $total_charges
                      + $total_remboursements_emprunts
                      + $total_virements_sortie
                      + $total_comptes_courants_associes_sortie
                      + $total_autres_sorties;

      if($mois == 1){
        $solde_depart = $solde_debut_annee->getMontant();
      }

      $total_compte = $solde_depart+$total_entree+$total_sortie; //on additionne car $total_sortie est déjà un nombre négatif
      $solde_depart = $total_compte;
      $flux_net = $total_entree+$total_sortie; //addition car $total_sortie est négatif

      $arr_accurate[$mois] = array(
          'FACTURES' => $total_factures,
          'AVOIRS_FOURNISSEURS' => $total_avoirs_fournisseurs,
          'REMBOURSEMENT_TAXES' => $total_remboursement_taxes,
          'DECAISSEMENTS_EMPRUNTS' => $total_decaissements_emprunts,
          'DECAISSEMENTS_SUBVENTIONS' => $total_decaissements_subventions,
          'VIREMENTS_ENTREE' => $total_virements_entree,
          'COMPTES_COURANTS_ASSOCIES_ENTREE' => $total_comptes_courants_associes_entree,
          'AUTRES_ENTREES' => $total_autres_entrees,
          'DEPENSES' => $total_depenses,
          'AVOIRS_CLIENTS' => $total_avoirs_clients,
          'TAXES' => $total_taxes,
          'SALAIRES' => $total_salaires,
          'CHARGES' => $total_charges,
          'REMBOURSEMENTS_EMPRUNTS' => $total_remboursements_emprunts,
          'VIREMENTS_SORTIE' => $total_virements_sortie,
          'COMPTES_COURANTS_ASSOCIES_SORTIE' => $total_comptes_courants_associes_sortie,
          'AUTRES_SORTIES' => $total_autres_sorties,
          'TOTAL_ENTREES' => $total_entree,
          'TOTAL_SORTIES' => $total_sortie,
          'TOTAL_COMPTE' => $total_compte,
          'FLUX_NET' => $flux_net
      );
    }

    return $arr_accurate;
  }

  public function calculTresoreriePrevisonnelle($compteBancaire, $year){

    $prevTresoRepo = $this->em->getRepository('AppBundle:Compta\PrevTreso');

    $soldeRepo = $this->em->getRepository('AppBundle:Compta\SoldeCompteBancaire');
    $solde_debut_annee = $soldeRepo->findOneBy(array(
        'compteBancaire' => $compteBancaire,
        'date' => \DateTime::createFromFormat('Y-m-d', $year.'-01-01')
    ));

    if($solde_debut_annee == null){
      throw new \Exception("Vous n'avez pas indiqué le solde du compte bancaire "+$compteBancaire->getNom()+" au 1er janvier "+$year);
    }

    $arr_prev = array();

    $solde_depart = $solde_debut_annee->getMontant();
    for($mois=1; $mois<=12; $mois++){

      $total_entree = 0;
      $total_sortie = 0;

      $arr_prev_mois = $prevTresoRepo->findBy(array(
        'compteBancaire' => $compteBancaire,
        'mois' => $mois,
        'annee' => $year
      ));

      $arr_prev[$mois] = array();

      foreach($arr_prev_mois as $prev){
        $arr_prev[$mois][$prev->getPoste()] = $prev->getMontant();

        if( in_array($prev->getPoste(), $this->arr_postes_entree) ){
          $total_entree+=$prev->getMontant();
        } else if( in_array($prev->getPoste(), $this->arr_postes_sortie) ){
          $total_sortie+=$prev->getMontant();
        } else {
          throw Exception('Poste inconnu : '.$prev->getPoste());
        }
      }

      $arr_prev[$mois]['TOTAL_ENTREES'] = $total_entree;
      $arr_prev[$mois]['TOTAL_SORTIES'] = $total_sortie;
      $arr_prev[$mois]['FLUX_NET'] = $total_entree+$total_sortie; //addition car $total_sortie est négatif

      $total_compte  = $solde_depart
                      + $total_entree
                      + $total_sortie;
      $solde_depart = $total_compte;
      $arr_prev[$mois]['TOTAL_COMPTE'] = $total_compte;
    }

    return $arr_prev;
  }

  public function ajouterTresoreriePrevisionnelle($valeur, $mois, $poste, $compteBancaire_id, $year){

    $prevTresoRepo = $this->em->getRepository('AppBundle:Compta\PrevTreso');

    $prevTreso= $prevTresoRepo->findOneBy(array(
      'compteBancaire' => $compteBancaire_id,
      'mois' => $mois,
      'annee' => $year,
      'poste' => $poste
    ));

    if($prevTreso && $valeur === null){
      $this->em->remove($prevTreso);

    } else {

      if($prevTreso == null){
        $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
        $compteBancaire = $compteBancaireRepo->find($compteBancaire_id);

        $prevTreso = new PrevTreso();
        $prevTreso->setCompteBancaire($compteBancaire);
        $prevTreso->setMois($mois);
        $prevTreso->setAnnee($year);
        $prevTreso->setPoste($poste);
      }

      if( in_array($poste, $this->arr_postes_entree) ){
        $prevTreso->setMontant($valeur);
      } else if( in_array($poste, $this->arr_postes_sortie) ){
          $prevTreso->setMontant(0-$valeur);
      } else {
        throw Exception('Poste inconnu : '.$poste);
      }

      $this->em->persist($prevTreso);
    }

    $this->em->flush();
  }

  public function calculTotaux($mois, $poste, $compteBancaire_id, $year){

    $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
    $compteBancaire = $compteBancaireRepo->find($compteBancaire_id);

    $arr_prev = $this->calculTresoreriePrevisonnelle($compteBancaire, $year);

    return array(
      'totalMois' => $arr_prev[$mois]['TOTAL_COMPTE'],
      'totalEntrees' => $arr_prev[$mois]['TOTAL_ENTREES'],
      'totalSorties' => $arr_prev[$mois]['TOTAL_SORTIES'],
      'fluxNet' => $arr_prev[$mois]['FLUX_NET']
    );

  }

}
