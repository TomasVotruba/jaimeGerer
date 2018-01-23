<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use AppBundle\Entity\Compta\Rapprochement;
use AppBundle\Entity\Compta\MouvementBancaire;

use AppBundle\Form\Compta\MouvementBancaireType;


class RapprochementController extends Controller
{

    /**
    * @Route("/compta/rapprochement", name="compta_rapprochement_index")
    */
    public function rapprochementIndexAction()
    {
        /*creation du dropdown pour choisir le compte bancaire*/
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
        $arr_comptesBancaires = $repo->findByCompany($this->getUser()->getCompany());

        $session = $this->getRequest()->getSession();
        $filtreReleveBancaire['montant'] = 'all';
        $filtreReleveBancaire['rapprochement'] = 'non-rapproche';
        $filtreReleveBancaire['id'] = 0;
        
        $startDate = date('Y-m-d',strtotime('-3 months'));
        $endDate = date('Y-m-d');
        $filtreReleveBancaire['dateRange'] = array('start' => $startDate, 'end' => $endDate);

        $session->set('FiltreReleveBancaire', $filtreReleveBancaire);

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('comptes', 'entity', array(
            'required' => true,
            'class' => 'AppBundle:Compta\CompteBancaire',
            'label' => 'Compte bancaire',
            'choices' => $arr_comptesBancaires,
            'attr' => array('class' => 'compte-select'),
            'data' => $this->getDoctrine()->getManager()->getReference("AppBundle:Compta\CompteBancaire", $filtreReleveBancaire['id'])
        ));

        /*pour afficher le solde du compte bancaire*/
        $arr_soldes_id = array();
        $soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
        foreach($arr_comptesBancaires as $compteBancaire){
          $solde = $soldeRepo->findLatest($compteBancaire);
          $arr_soldes_id[$compteBancaire->getId()] = $solde->getMontant();
        }

        /*creation des listes pour les dropdowns des objets rapprochables*/
        //remises de cheque
        $repoRemiseCheque = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');
        $arr_all_remises_cheques = $repoRemiseCheque->findForCompany($this->getUser()->getCompany());
        $arr_remises_cheques = array();
        $arr_factures_rapprochees_par_remises_cheques = array();
        $arr_avoirs_rapprochees_par_remises_cheques = array();
        foreach($arr_all_remises_cheques as $remiseCheque){
          if($remiseCheque->getTotalRapproche() < $remiseCheque->getTotal()){
            $arr_remises_cheques[] = $remiseCheque;
          } else {
            foreach($remiseCheque->getCheques() as $cheque){
              foreach($cheque->getPieces() as $piece){
                if($piece->getFacture()){
                  $arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
                }else if($piece->getAvoir()){
                  $arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
                }
              }
            }
          }
        }

        //factures
        $repoFactures = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
        $arr_all_factures = $repoFactures->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
        $arr_factures = array();
        foreach($arr_all_factures as $facture){
          if($facture->getTotalRapproche() < $facture->getTotalTTC() && $facture->getEtat() != "PAID" && !in_array($facture->getId(), $arr_factures_rapprochees_par_remises_cheques) && $facture->getTotalAvoirs() < $facture->getTotalTTC()){
            $arr_factures[] = $facture;
          }
        }

        //depenses
        $repoDepenses = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
        $arr_all_depenses = $repoDepenses->findForCompany($this->getUser()->getCompany());
        $arr_depenses = array();
        foreach($arr_all_depenses as $depense){
          if($depense->getEtat() != 'RAPPROCHE'){
            if($depense->getTotalRapproche() < $depense->getTotalTTC()){
              $arr_depenses[] = $depense;
            }
          }
        }

        //notes de frais
        $repoNotesFrais = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\NoteFrais');
        $arr_all_note_frais = $repoNotesFrais->findForCompany($this->getUser()->getCompany());
        $arr_notes_frais = array();
        foreach($arr_all_note_frais as $ndf){
          if($ndf->getEtat() == 'VALIDE'){
            $arr_notes_frais[] = $ndf;
          }
        }

        //accomptes
        $repoAccomptes = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Accompte');
        $arr_all_accomptes = $repoAccomptes->findForCompany($this->getUser()->getCompany());
        $arr_accomptes = array();
        foreach($arr_all_accomptes as $accompte){
          if($accompte->getTotalRapproche() < $accompte->getMontant()){
            $arr_accomptes[] = $accompte;
          }
        }

        //avoirs fournisseurs
        $repoAvoirs = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Avoir');
        $arr_all_avoirs_fournisseurs = $repoAvoirs->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
        $arr_avoirs_fournisseurs = array();
        foreach($arr_all_avoirs_fournisseurs as $avoir){
          if($avoir->getTotalRapproche() < $avoir->getTotalTTC() && !in_array($avoir->getId(), $arr_avoirs_rapprochees_par_remises_cheques)){
            $arr_avoirs_fournisseurs[] = $avoir;
          }
        }

        //avoirs clients
        $arr_all_avoirs_clients = $repoAvoirs->findForCompany('CLIENT', $this->getUser()->getCompany());
        $arr_avoirs_clients = array();
        foreach($arr_all_avoirs_clients as $avoir){
          if($avoir->getTotalRapproche() < $avoir->getTotalTTC()){
            $arr_avoirs_clients[] = $avoir;
          }
        }

        //affectations diverses vente
        $repoAffectations = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\AffectationDiverse');
        $arr_aff_ventes = $repoAffectations->findForCompany('VENTE', $this->getUser()->getCompany(), true);
        //affectations diverses  achats
        $arr_aff_achats = $repoAffectations->findForCompany('ACHAT', $this->getUser()->getCompany(), true);

        return $this->render('compta/rapprochement/compta_rapprochement_index.html.twig', array(
          'form' => $formBuilder->getForm()->createView(),
          'arr_soldes' => $arr_soldes_id,
          'arr_factures' => $arr_factures,
          'arr_depenses' => $arr_depenses,
          'arr_accomptes' => $arr_accomptes,
          'arr_avoirs_fournisseurs' => $arr_avoirs_fournisseurs,
          'arr_avoirs_clients' => $arr_avoirs_clients,
          'arr_remise_cheques' => $arr_remises_cheques,
          'arr_affectations_diverses_vente' => $arr_aff_ventes,
          'arr_affectations_diverses_achat' => $arr_aff_achats,
          'arr_notes_frais' => $arr_notes_frais,
          'filtreReleveBancaire' => $filtreReleveBancaire,
        ));
    }

  /**
   * @Route("/compta/rapprochement/voir", name="compta_rapprochement_voir", options={"expose"=true})
   */
  public function rapprochementVoirAction()
  {
    $compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
    $id = $this->getRequest()->request->get('id');

    $compteBancaire = $compteBancaireRepo->find($id);

    $arr_filtres = $this->getRequest()->request->get('filtres');
    $arr_filtres['id'] = $id;
    $session = $this->getRequest()->getSession();
    $session->set('FiltreReleveBancaire', $arr_filtres);

    //affichage de la liste des mouvements bancaires du compte
    $mouvementRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\MouvementBancaire');

    $arr_allMouvementsBancaires = $mouvementRepo->findBy(
        array('compteBancaire' => $compteBancaire),
        array('date' => 'DESC')
    );

    $arr_mouvementsBancaires_prefiltre = array();
    $arr_mouvementsBancaires = array();

    if($arr_filtres['rapprochement'] == 'rapproche'){
      foreach($arr_allMouvementsBancaires as $mouvement){
        if(count($mouvement->getRapprochements()) != 0){
          $arr_mouvementsBancaires_prefiltre[] = $mouvement;
        }
      }
    } else if($arr_filtres['rapprochement'] == 'non-rapproche'){
      foreach($arr_allMouvementsBancaires as $mouvement){
        if(count($mouvement->getRapprochements()) == 0){
          $arr_mouvementsBancaires_prefiltre[] = $mouvement;
        }
      }
    } else if($arr_filtres['rapprochement'] == 'all'){
      foreach($arr_allMouvementsBancaires as $mouvement){
        $arr_mouvementsBancaires_prefiltre[] = $mouvement;
      }
    }

    if($arr_filtres['montant'] == 'positif'){
      foreach($arr_mouvementsBancaires_prefiltre as $mouvement){
        if($mouvement->getMontant() > 0){
          $arr_mouvementsBancaires[] = $mouvement;
        }
      }
    } else if($arr_filtres['montant'] == 'negatif'){
      foreach($arr_mouvementsBancaires_prefiltre as $mouvement){
        if($mouvement->getMontant() < 0){
          $arr_mouvementsBancaires[] = $mouvement;
        }
      }
    } else if($arr_filtres['montant'] == 'all'){
      foreach($arr_mouvementsBancaires_prefiltre as $mouvement){
        $arr_mouvementsBancaires[] = $mouvement;
      }
    }

    if(is_array($arr_filtres['dateRange'])){
      $arr_mouvementsBancaires_prefiltre = array();
      foreach($arr_mouvementsBancaires as $mouvement){
        if($mouvement->getDate() >= \DateTime::createFromFormat('D M d Y H:i:s e+', $arr_filtres['dateRange']['start']) && $mouvement->getDate() <= \DateTime::createFromFormat('D M d Y H:i:s e+', $arr_filtres['dateRange']['end'])){
          $arr_mouvementsBancaires_prefiltre[] = $mouvement;
        }
      }
      $arr_mouvementsBancaires = $arr_mouvementsBancaires_prefiltre;
    }

    return $this->render('compta/rapprochement/compta_rapprochement_voir.html.twig', array(
        'arr_mouvementsBancaires' => $arr_mouvementsBancaires,
    ));
  }


    /**
    * @Route("/compta/rapprochement/supprimer/{id}", name="compta_rapprochement_supprimer", options={"expose"=true})
    */
    public function rapprochementSupprimerAction(Rapprochement $rapprochement)
    {
        $em = $this->getDoctrine()->getManager();

        //supprimer les lignes du journal de banque
        $mouvement = $rapprochement->getMouvementBancaire();
        $journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');

        $arr_journalBanque = $journalBanqueRepo->findByMouvementBancaire($mouvement);
        foreach($arr_journalBanque as $ligneJournal){
            $em->remove($ligneJournal);
        }

        $mouvement->setType(null);
        $em->persist($mouvement);

        if($rapprochement->getDepense()){
            $depense = $rapprochement->getDepense();
            $depense->setEtat("ENREGISTRE");
            $em->persist($depense);
        }
        if($rapprochement->getFacture()){
            $facture = $rapprochement->getFacture();
            $facture->setEtat("ENREGISTRE");
            $em->persist($facture);
        }
        if($rapprochement->getNoteFrais()){
            $ndf = $rapprochement->getNoteFrais();
            $ndf->setEtat("ENREGISTRE");
            $em->persist($ndf);
        }

        //supprimer le rapprochement
        $em->remove($rapprochement);

        $em->flush();

        return new JsonResponse();
    }

  /**
   * @Route("/compta/mouvement-bancaire/rapprocher/{id}/{type}/{piece}", name="compta_mouvement_bancaire_rapprocher", options={"expose"=true})
   */
  public function mouvementBancaireRapprocherAction(MouvementBancaire $mouvementBancaire, $type, $piece)
  {
    //mise à jour du mouvement bancaire
    $mouvementBancaire->setType($type);
    $em = $this->getDoctrine()->getManager();
    $em->persist($mouvementBancaire);

    //creation et hydratation du rapprochement bancaire
    $rapprochement = new Rapprochement();
    $rapprochement->setDate(new \DateTime(date('Y-m-d')));
    $rapprochement->setMouvementBancaire($mouvementBancaire);
    $s_piece = ''; //string pour l'affichage dans le tableau du relevé bancaire
    switch($type){
        case 'DEPENSE' :
            $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
            $depense = $repo->find($piece);
            if($depense->getCompte()->getCompteComptableFournisseur() == null){
                return new JsonResponse(array(
                    'message' => 'Pas de compte comptable fournisseur associé à '.$depense->getCompte()), 
                    419
                );
            }
            $rapprochement->setDepense($depense);
            $piece = $depense;
            $s_piece =  $piece->getCompte()->getNom().' : '.$piece->getTotalTTC().' € TTC';
            $depense->setEtat('RAPPROCHE');
            $em->persist($depense);
            break;

      case 'FACTURE' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
        $facture = $repo->find($piece);
        if($facture->getCompte()->getCompteComptableClient() == null){
            return new JsonResponse(array(
                'message' => 'Pas de compte comptable client associé à '.$facture->getCompte()->getNom()), 
                419
            );
        }
        $rapprochement->setFacture($facture);
        $piece = $facture;
        $s_piece =  $piece->getNum().' : '.$piece->getTotalTTC().' € TTC';
        $facture->setEtat('RAPPROCHE');
        $em->persist($facture);
        break;
      case 'ACCOMPTE' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Accompte');
        $accompte = $repo->find($piece);
        $rapprochement->setAccompte($accompte);
        $piece = $accompte;
        $s_piece = $accompte->__toString();
        break;
      case 'AVOIR-FOURNISSEUR' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Avoir');
        $avoir = $repo->find($piece);
        if($avoir->getDepense()->getCompte()->getCompteComptableFournisseur() == null){
            return new JsonResponse(array(
                'message' => 'Pas de compte comptable fournisseur associé à '.$avoir->getDepense()->getCompte()->getNom()), 
                419
            );
        }
        $rapprochement->setAvoir($avoir);
        $piece = $avoir;
        $s_piece = $avoir->__toString();
        break;
      case 'AVOIR-CLIENT' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Avoir');
        $avoir = $repo->find($piece);
        if($avoir->getFacture()->getCompte()->getCompteComptableClient() == null){
            return new JsonResponse(array(
                'message' => 'Pas de compte comptable client associé à '.$avoir->getFacture()->getCompte()->getNom()), 
                419
            );
        }
        $rapprochement->setAvoir($avoir);
        $piece = $avoir;
        $s_piece = $avoir->__toString();
        break;
      case 'REMISE-CHEQUES' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');
        $remiseCheque = $repo->find($piece);
        $rapprochement->setRemiseCheque($remiseCheque);
        $piece = $remiseCheque;
        $s_piece = $remiseCheque->__toString();
        break;
      case 'AFFECTATION-DIVERSE-VENTE' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\AffectationDiverse');
        $affectationDiverse = $repo->find($piece);
        $rapprochement->setAffectationDiverse($affectationDiverse);
        $piece = $affectationDiverse;
        $s_piece = $affectationDiverse->__toString();
        break;
      case 'AFFECTATION-DIVERSE-ACHAT' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\AffectationDiverse');
        $affectationDiverse = $repo->find($piece);
        $rapprochement->setAffectationDiverse($affectationDiverse);
        $piece = $affectationDiverse;
        $s_piece = $affectationDiverse->__toString();
        break;
      case 'NOTE-FRAIS' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\NoteFrais');
        $noteFrais = $repo->find($piece);
        $rapprochement->setNoteFrais($noteFrais);
        $piece = $noteFrais;
        $s_piece = $noteFrais->__toString();
        $noteFrais->setEtat('RAPPROCHE');
        $em->persist($noteFrais);
        break;
    }

    $em->persist($rapprochement);


    //faut-il supprimer l'objet des dropdowns ?
    $b_remove = true;
    if($type != 'AFFECTATION-DIVERSE-VENTE' && $type != 'AFFECTATION-DIVERSE-ACHAT'){
      if($piece->getTotalRapproche() < $piece->getTotalTTC()){
        $b_remove = false;
      }
    }

    try{
      $journalBanqueService = $this->container->get('appbundle.compta_journal_banque_controller');
      $journalBanqueService->journalBanqueAjouterAction($type, $rapprochement);
      $em->flush();

    } catch(\Exception $e){
      throw $e;
    }

    return new JsonResponse(array(
      'piece_id' => $piece->getId(),
      's_piece' => $s_piece,
      'remove' => $b_remove
    ));
  }

  /**
   * @Route("/compta/mouvement-bancaire/scinder/{id}", name="compta_mouvement_bancaire_scinder")
   */
  public function mouvementBancaireScinderAction(MouvementBancaire $mouvementBancaire)
  {
    $formBuilder = $this->createFormBuilder();
    $formBuilder->add('mouvements', 'collection', array(
          'type' => new MouvementBancaireType($mouvementBancaire),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden')
             ));
    $form = $formBuilder->getForm();

    $request = $this->getRequest();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $arr_mouvements = $form->get('mouvements')->getData();

      foreach($arr_mouvements as $newMouvement){
        $newMouvement->setCompteBancaire($mouvementBancaire->getCompteBancaire());
        if($mouvementBancaire->getMontant() < 0){
          $montant = $newMouvement->getMontant();
          $montant = -$montant;
          $newMouvement->setMontant($montant);
        }
        $em->persist($newMouvement);
      }

      $em->remove($mouvementBancaire);
      $em->flush();

      return $this->redirect($this->generateUrl('compta_rapprochement_index'));
    }

    return $this->render('compta/mouvement_bancaire/compta_mouvement_bancaire_scinder_modal.html.twig', array(
      'mouvement' => $mouvementBancaire,
      'form' => $form->createView(),
    ));
  }

  /**
   * @Route("/compta/mouvement-bancaire/fusionner/{id}", name="compta_mouvement_bancaire_fusionner")
   */
  public function mouvementBancaireFusionnerAction(MouvementBancaire $mouvementBancaire)
  {
    $em = $this->getDoctrine()->getManager();
    $mouvementsRepo = $em->getRepository('AppBundle:Compta\MouvementBancaire');

    $arr_mouvements = $mouvementsRepo->findBy(
        array('compteBancaire' => $mouvementBancaire->getCompteBancaire(), 'type' => null),
        array('date' => 'DESC')
    );

    $arr_choices = array();
    foreach($arr_mouvements as $mouvement){
      if($mouvement->getId() != $mouvementBancaire->getId()){
        $arr_choices[$mouvement->getId()] = $mouvement;
      }
    }

    $formBuilder = $this->createFormBuilder();
    $formBuilder ->add('fusion', 'choice', array(
                'required' => true,
                'label' => 'Fusionner avec :',
          'multiple' => true,
                'choices' => $arr_choices,
          'attr' => array(
            'size' => '12'
          )
             ));
    $form = $formBuilder->getForm();

    $request = $this->getRequest();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $arr_fusion = $form->get('fusion')->getData();

      $newMontant = $mouvementBancaire->getMontant();
      foreach($arr_fusion as $fusionId){
        $fusionMouvement = $mouvementsRepo->find($fusionId);
        $newMontant+=$fusionMouvement->getMontant();
        $em->remove($fusionMouvement);
      }
      $mouvementBancaire->setMontant($newMontant);
      $em->persist($mouvementBancaire);
      $em->flush();

      return $this->redirect($this->generateUrl('compta_rapprochement_index'));
    }

    return $this->render('compta/mouvement_bancaire/compta_mouvement_bancaire_fusionner_modal.html.twig', array(
        'mouvement' => $mouvementBancaire,
        'form' => $form->createView(),
    ));
  }


  /**
    * @Route("/compta/rapprochement-avance", name="compta_rapprochement_avance", options={"expose"=true})
    */
    public function rapprochementAvanceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $mouvementRepo = $em->getRepository('AppBundle:Compta\MouvementBancaire');
        $compteBancaireRepo = $em->getRepository('AppBundle:Compta\CompteBancaire');
        $remiseChequeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');
        $factureRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
        $depenseRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
        $noteFraisRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:NDF\NoteFrais');
        $avoirRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Avoir');
        $affectationDiverseRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\AffectationDiverse');

        $arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());
        $arr_mouvementsBancaires = array();
        foreach($arr_comptesBancaires as $compteBancaire){
            
            $arr_mouvementsBancaires[$compteBancaire->getNom()] = array();
            $arrMouvementsCompteBancaire = $mouvementRepo->findBy(
                array('compteBancaire' => $compteBancaire),
                array('date' => 'DESC')
            );
        
            foreach($arrMouvementsCompteBancaire as $mouvement){
                if(count($mouvement->getRapprochements()) == 0 ){
                    $arr_mouvementsBancaires[$mouvement->getCompteBancaire()->getNom()][] = $mouvement;
                }
            }
        }

        $arr_pieces = array(
            'FACTURES' => array(),
            'DEPENSES' => array(),
            'NOTES-FRAIS' => array(),
            'REMISES-CHEQUES' => array(),
            'AVOIRS-FOURNISSEUR' => array(),
            'AVOIRS-CLIENT' => array(),
            'AFFECTATIONS-DIVERSES-VENTE' => array(),
            'AFFECTATIONS-DIVERSES-ACHAT' => array(),
        );

        //remises de cheque
        $arr_all_remises_cheques = $remiseChequeRepo->findForCompany($this->getUser()->getCompany());
        $arr_factures_rapprochees_par_remises_cheques = array();
        $arr_avoirs_rapprochees_par_remises_cheques = array();
        foreach($arr_all_remises_cheques as $remiseCheque){
          if($remiseCheque->getTotalRapproche() < $remiseCheque->getTotal()){
     //       $arr_pieces['REMISES-CHEQUES'][] = $remiseCheque;
          } else {
            foreach($remiseCheque->getCheques() as $cheque){
              foreach($cheque->getPieces() as $piece){
                if($piece->getFacture()){
                  $arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
                }else if($piece->getAvoir()){
                  $arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
                }
              }
            }
          }
        }

        //factures
        $arr_all_factures = $factureRepo->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
        foreach($arr_all_factures as $facture){
          if($facture->getTotalRapproche() < $facture->getTotalTTC() && $facture->getEtat() != "PAID" && !in_array($facture->getId(), $arr_factures_rapprochees_par_remises_cheques) && $facture->getTotalAvoirs() < $facture->getTotalTTC()){
            $arr_pieces['FACTURES'][] = $facture;
          }
        }

        //depenses
        $arr_all_depenses = $depenseRepo->findForCompany($this->getUser()->getCompany());
        foreach($arr_all_depenses as $depense){
          if($depense->getEtat() != 'RAPPROCHE'){
            if($depense->getTotalRapproche() < $depense->getTotalTTC()){
              $arr_pieces['DEPENSES'][] = $depense;
            }
          }
        }

        //notes de frais
        // $arr_all_note_frais = $noteFraisRepo->findForCompany($this->getUser()->getCompany());
        // foreach($arr_all_note_frais as $ndf){
        //   if($ndf->getEtat() == 'VALIDE'){
        //     $arr_pieces['NOTES-FRAIS'][] = $ndf;
        //   }
        // }

        // //avoirs fournisseurs
        // $arr_all_avoirs_fournisseurs = $avoirRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
        // foreach($arr_all_avoirs_fournisseurs as $avoir){
        //   if($avoir->getTotalRapproche() < $avoir->getTotalTTC() && !in_array($avoir->getId(), $arr_avoirs_rapprochees_par_remises_cheques)){
        //     $arr_pieces['AVOIRS-FOURNISSEUR'][] = $avoir;
        //   }
        // }

        // //avoirs clients
        // $arr_all_avoirs_clients = $avoirRepo->findForCompany('CLIENT', $this->getUser()->getCompany());
        // foreach($arr_all_avoirs_clients as $avoir){
        //   if($avoir->getTotalRapproche() < $avoir->getTotalTTC()){
        //     $arr_pieces['AVOIRS-CLIENT'][] = $avoir;
        //   }
        // }

        // //affectations diverses vente
        // $arr_affectations_diverses_vente = $affectationDiverseRepo->findForCompany('VENTE', $this->getUser()->getCompany(), true);
        // foreach($arr_affectations_diverses_vente as $affectationDiverse){
        //     $arr_pieces['AFFECTATIONS-DIVERSES-VENTE'][] = $affectationDiverse;
        // }
         
        // //affectations diverses  achats
        // $arr_affectations_diverses_achat = $affectationDiverseRepo->findForCompany('ACHAT', $this->getUser()->getCompany(), true);
        // foreach($arr_affectations_diverses_achat as $affectationDiverse){
        //     $arr_pieces['AFFECTATIONS-DIVERSES-ACHAT'][] = $affectationDiverse;
        // }

        return $this->render('compta/rapprochement/compta_rapprochement_avance.html.twig', array(
            'arr_mouvementsBancaires' => $arr_mouvementsBancaires,
            'arr_pieces' => $arr_pieces,
        ));
        
    }

    /**
     * @Route("/compta/rapprochement-avance/rapprocher", name="compta_rapprochement_avance_rapprocher", options={"expose"=true})
     */
    public function rapprochementAvanceRapprocherAction()
    {
        $em = $this->getDoctrine()->getManager();
        $mouvementBancaireRepo = $em->getRepository('AppBundle:Compta\MouvementBancaire');

        $arr_mouvementsId = $this->getRequest()->request->get('mouvements');
        $arr_piecesId = $this->getRequest()->request->get('pieces');

        $arr_cc = array();
        $arr_pieces = array();
        $arr_mouvements = array();


        foreach($arr_mouvementsId as $mouvementId){
            $mouvementBancaire = $mouvementBancaireRepo->find($mouvementId);
            $arr_mouvements[] = $mouvementBancaire;

            foreach($arr_piecesId as $pieceId){
                $arr_explode = explode('_', $pieceId);
                $type = $arr_explode[0];
                $id = $arr_explode[1];

                //creation et hydratation du rapprochement bancaire
                $rapprochement = new Rapprochement();
                $rapprochement->setDate(new \DateTime(date('Y-m-d')));
                $rapprochement->setMouvementBancaire($mouvementBancaire);

                switch($type){
                  case 'DEPENSES' :
                    $repo = $em->getRepository('AppBundle:Compta\Depense');
                    $piece = $repo->find($id);
                    $rapprochement->setDepense($piece);
                    $piece->setEtat('RAPPROCHE');
                    $em->persist($piece);
                    if( !in_array($piece->getCompte()->getCompteComptableFournisseur()->getId(), $arr_cc) ){
                       $arr_cc[] = $piece->getCompte()->getCompteComptableFournisseur()->getId(); 
                    }
                    break;
                  
                  case 'FACTURES' :
                    $repo = $em->getRepository('AppBundle:CRM\DocumentPrix');
                    $piece = $repo->find($id);
                    $rapprochement->setFacture($piece);
                    $piece->setEtat('RAPPROCHE');
                    $em->persist($piece);
                    if( !in_array($piece->getCompte()->getCompteComptableClient()->getId(), $arr_cc) ){
                       $arr_cc[] = $piece->getCompte()->getCompteComptableClient()->getId(); 
                    }
                    break;
                  
                  // case 'AVOIRS-FOURNISSEUR' :
                  //   $repo = $em->getRepository('AppBundle:Compta\Avoir');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setAvoir($piece);
                  //   break;
                  
                  // case 'AVOIRS-CLIENT' :
                  //   $repo = $em->getRepository('AppBundle:Compta\Avoir');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setAvoir($piece);
                  //   break;
                 
                  // case 'REMISES-CHEQUES' :
                  //   $repo = $em->getRepository('AppBundle:Compta\RemiseCheque');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setRemiseCheque($piece);
                  //   break;

                  // case 'AFFECTATIONS-DIVERSES-VENTE' :
                  //   $repo = $em->getRepository('AppBundle:Compta\AffectationDiverse');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setAffectationDiverse($piece);
                  //   break;

                  // case 'AFFECTATIONS-DIVERSES-ACHAT' :
                  //   $repo = $em->getRepository('AppBundle:Compta\AffectationDiverse');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setAffectationDiverse($piece);
                  //   break;

                  // case 'NOTES-FRAIS' :
                  //   $repo = $em->getRepository('AppBundle:NDF\NoteFrais');
                  //   $piece = $repo->find($id);
                  //   $rapprochement->setNoteFrais($piece);
                  //   break;
                }

                $arr_pieces[] = array($type => $piece);
                $em->persist($rapprochement);
            }
        }

        //ne pas rapprocher et lettrer si les pièces viennent de comptes comptables différents
        if(count($arr_cc) > 1){
            return new JsonResponse(array(
                'message' => 'Les pièces ne doivent pas venir de comptes comptables différents.'), 
                419
            );
        }

        try{
            $journalBanqueService = $this->container->get('appbundle.compta_journal_banque_controller');
            $journalBanqueService->journalBanqueAjouterPlusieursPiecesMemeCompteAction($arr_mouvements, $arr_pieces);
            $em->flush();

        } catch(\Exception $e){
            throw $e;
        }
            
        return new JsonResponse();

    }

}
