<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use AppBundle\Entity\Compta\Rapprochement;


class RapprochementController extends Controller
{

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
        $rapprochement->setDepense($depense);
        $piece = $depense;
        $s_piece =  $piece->getCompte()->getNom().' : '.$piece->getTotalTTC().' € TTC';
        $depense->setEtat('RAPPROCHE');
        $em->persist($depense);
        break;
      case 'FACTURE' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
        $facture = $repo->find($piece);
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
        $rapprochement->setAvoir($avoir);
        $piece = $avoir;
        $s_piece = $avoir->__toString();
        break;
      case 'AVOIR-CLIENT' :
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Avoir');
        $avoir = $repo->find($piece);
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

      return $this->redirect($this->generateUrl('compta_releve_bancaire_index'));
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

      return $this->redirect($this->generateUrl('compta_releve_bancaire_index'));
    }

    return $this->render('compta/mouvement_bancaire/compta_mouvement_bancaire_fusionner_modal.html.twig', array(
        'mouvement' => $mouvementBancaire,
        'form' => $form->createView(),
    ));
  }

}
