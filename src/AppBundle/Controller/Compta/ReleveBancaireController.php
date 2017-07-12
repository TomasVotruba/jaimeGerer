<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Compta\MouvementBancaire;
use AppBundle\Entity\Compta\CompteBancaire;
use AppBundle\Entity\Compta\SoldeCompteBancaire;
use AppBundle\Entity\Compta\Rapprochement;

use AppBundle\Form\Compta\UploadReleveBancaireType;
use AppBundle\Form\Compta\UploadReleveBancaireMappingType;
use AppBundle\Form\Compta\MouvementBancaireType;


require_once __DIR__.'/../../../../vendor/parsecsv/php-parsecsv/parsecsv.lib.php';

class ReleveBancaireController extends Controller
{
	/**
	 * @Route("/compta/releve-bancaire", name="compta_releve_bancaire_index")
	 */
	public function releveBancaireIndexAction()
	{
		/*creation du dropdown pour choisir le compte bancaire*/
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$arr_comptesBancaires = $repo->findByCompany($this->getUser()->getCompany());

		$session = $this->getRequest()->getSession();
		$filtreReleveBancaire = $session->get('FiltreReleveBancaire');
		if( is_null($filtreReleveBancaire) ){
			$filtreReleveBancaire['montant'] = 'all';
			$filtreReleveBancaire['rapprochement'] = 'all';
			$filtreReleveBancaire['id'] = 0;
			$filtreReleveBancaire['dateRange'] = '';

			$session->set('FiltreReleveBancaire', $filtreReleveBancaire);
		}

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
			if($ndf->getEtat() != 'RAPPROCHE'){
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

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_index.html.twig', array(
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
	 * @Route("/compta/releve-bancaire/voir", name="compta_releve_bancaire_voir", options={"expose"=true})
	 */
	public function releveBancaireVoirAction()
	{
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$id = $this->getRequest()->request->get('id');

		$compteBancaire = $compteBancaireRepo->find($id);

		$arr_filtres = $this->getRequest()->request->get('filtres');
		$session = $this->getRequest()->getSession();
		$FiltreReleveBancaire = $arr_filtres;
		$FiltreReleveBancaire['id'] = $id;
		$session->set('FiltreReleveBancaire', $FiltreReleveBancaire);
	
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

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_voir.html.twig', array(
				'arr_mouvementsBancaires' => $arr_mouvementsBancaires,
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/importer/form", name="compta_releve_bancaire_importer_form")
	 */
	public function releveBancaireImporterFormAction()
	{
		$form = $this->createForm(new UploadReleveBancaireType($this->getUser()->getCompany()));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			//recuperation des données du formulaire
			$data = $form->getData();
			$compteBancaire = $data['compteBancaire'];
			$file = $data['file'];
			$solde = $data['solde'];
			$dateFormat = $data['dateFormat'];

			//mise à jour du compte bancaire si le solde a été modifié
			$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
			$latestSolde = $soldeRepo->findLatest($compteBancaire);
			if($solde != $latestSolde->getMontant()){
				$newSolde = new SoldeCompteBancaire();
				$newSolde->setCompteBancaire($compteBancaire);
				$newSolde->setDate(new \DateTime(date('Y-m-d')));
				$newSolde->setMontant($solde);
				$em = $this->getDoctrine()->getManager();
				$em->persist($newSolde);
				$em->flush();
			}

			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$compteBancaire->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/releve_bancaire';
			$file->move($path, $filename);

			$session = $request->getSession();
			$session->set('import_releve_filename', $filename);
			$session->set('import_releve_compte_bancaire_id', $compteBancaire->getId());
			$session->set('import_releve_compte_date_format', $dateFormat);


			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('compta_releve_bancaire_importer_mapping'));
		}

		//pour permettre de changer le solde du compte bancaire avant d'importer le fichier
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());
		$arr_soldes_id = array();
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		foreach($arr_comptesBancaires as $compteBancaire){
			$solde = $soldeRepo->findLatest($compteBancaire);
			$arr_soldes_id[$compteBancaire->getId()] = $solde->getMontant();
		}

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_form.html.twig', array(
			'form' => $form->createView(),
			'arr_soldes' => $arr_soldes_id
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/importer/mapping", name="compta_releve_bancaire_importer_mapping")
	 */
	public function releveBancaireImporterMappingAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$em = $this->getDoctrine()->getManager();
		
		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/releve_bancaire';
		$filename = $session->get('import_releve_filename');
		$fh = fopen($path.'/'.$filename, 'r+');

		//récupération de la première ligne pour créer le formulaire de mapping
		$arr_headers = array();
		$i = 0;
		while( ($row = fgetcsv($fh, 8192)) !== FALSE && $i<1 ) {
			//convert because CSV from Excel is not encoded in UTF8
			$row = array_map("utf8_encode", $row);
			$arr_headers = explode(';',$row[$i]);
			$i++;
		}
		fclose($fh);
		$arr_headers = array_combine($arr_headers, $arr_headers); //pour que l'array ait les mêmes clés et valeurs
		$form_mapping = $this->createForm(new UploadReleveBancaireMappingType($arr_headers));

		$request = $this->getRequest();
		$form_mapping->handleRequest($request);

		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {
			//recuperation des données du formulaire
			$data = $form_mapping->getData();

			$session->set('import_releve_compte_col_date', $data['date']);
			$session->set('import_releve_compte_col_libelle', $data['libelle']);
			$session->set('import_releve_compte_col_debit', $data['debit']);
			$session->set('import_releve_compte_col_credit', $data['credit']);
	
			return $this->redirect(
				$this->generateUrl('compta_releve_bancaire_importer_validation')
			);
		}

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_mapping.html.twig', array(
			'form' => $form_mapping->createView(),
		));

	}

	/**
	 * @Route("/compta/releve-bancaire/importer/validation", name="compta_releve_bancaire_importer_validation")
	 */
	 public function releveBancaireImporterValidationAction(){

	 	$session = $this->getRequest()->getSession();
		$releveBancaireService = $this->get('appbundle.compta_releve_bancaire_service');
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');

		$compte_bancaire_id = $session->get('import_releve_compte_bancaire_id');
		$compteBancaire = $compteBancaireRepo->find($compte_bancaire_id);

		$filename = $session->get('import_releve_filename');
	 	$dateFormat = $session->get('import_releve_compte_date_format');
	 	$colDate = $session->get('import_releve_compte_col_date');
	 	$colLibelle = $session->get('import_releve_compte_col_libelle');
	 	$colDebit = $session->get('import_releve_compte_col_debit');
	 	$colCredit = $session->get('import_releve_compte_col_credit');

		$arr_parsed = $releveBancaireService->parseReleveCSV($colDate, $colLibelle, $colDebit, $colCredit, $dateFormat, $filename, $compteBancaire);

		$ancienSolde = $soldeRepo->findLatest($compteBancaire);
		$nouveauSolde = $ancienSolde->getMontant()+$arr_parsed['total'];

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_validation.html.twig', array(
			'arr_mouvements' => $arr_parsed['arr_mouvements'],
			'total' => $arr_parsed['total'],
			'ancienSolde' => $ancienSolde->getMontant(),
			'nouveauSolde' => $nouveauSolde,
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/importer", name="compta_releve_bancaire_importer")
	 */
	 public function releveBancaireImporterAction(){

	 	$session = $this->getRequest()->getSession();
	 	$em = $this->getDoctrine()->getManager();
		$releveBancaireService = $this->get('appbundle.compta_releve_bancaire_service');
		$compteBancaireRepo = $em->getRepository('AppBundle:Compta\CompteBancaire');
		$soldeRepo = $em->getRepository('AppBundle:Compta\SoldeCompteBancaire');

		$compte_bancaire_id = $session->get('import_releve_compte_bancaire_id');
		$compteBancaire = $compteBancaireRepo->find($compte_bancaire_id);

		$filename = $session->get('import_releve_filename');
	 	$dateFormat = $session->get('import_releve_compte_date_format');
	 	$colDate = $session->get('import_releve_compte_col_date');
	 	$colLibelle = $session->get('import_releve_compte_col_libelle');
	 	$colDebit = $session->get('import_releve_compte_col_debit');
	 	$colCredit = $session->get('import_releve_compte_col_credit');

		$arr_parsed = $releveBancaireService->parseReleveCSV($colDate, $colLibelle, $colDebit, $colCredit, $dateFormat, $filename, $compteBancaire);

		foreach($arr_parsed['arr_mouvements'] as $mouvement){
			$em->persist($mouvement);
		}

		$ancienSolde = $soldeRepo->findLatest($compteBancaire);

		$newSolde = new SoldeCompteBancaire();
		$newSolde->setCompteBancaire($compteBancaire);
		$newSolde->setDate(new \DateTime(date('Y-m-d')));
		$newSolde->setMontant($ancienSolde->getMontant()+$arr_parsed['total']);
		$em->persist($newSolde);

		$em->flush();

		return $this->redirect($this->generateUrl('compta_releve_bancaire_index'));
	}
	
}
