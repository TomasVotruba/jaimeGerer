<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use AppBundle\Controller\CRM\ContactController;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\CRM\Rapport;
use AppBundle\Entity\CRM\PriseContact;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\RapportFilterType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;
use AppBundle\Form\CRM\RapportType;
use AppBundle\Form\CRM\ProspectionImporterMappingType;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

use AppBundle\Entity\CRM\Prospection;
use AppBundle\Form\CRM\ProspectionType;

use PHPExcel;
use PHPExcel_IOFactory;


class ProspectionController extends Controller
{

	/**
	 * @Route("/crm/prospection/importer/upload/{id}", name="crm_prospection_importer_upload")
	 */
	public function prospectionImporterUploadAction(Prospection $prospection)
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$arr_filenames = $session->get('prospection_import_contacts_filename');
		//~ var_dump($request->files); exit;
		foreach( $request->files as $file )
		{
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$arr_filenames[] = array('nom_original' => $file->getClientOriginalName(),
									 'nouveau_nom'  => $filename);
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/prospection_contact_import';
			$file->move($path, $filename);

		}
		$session->set('prospection_import_contacts_filename', $arr_filenames);
		exit;
	}

	/**
	 * @Route("/crm/prospection/importer/{id}", name="crm_prospection_importer")
	 */
	public function prospectionImporterAction(Prospection $prospection)
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$session->set('prospection_import_contacts_filename', array());

		return $this->render('crm/prospection/crm_prospection_importer.html.twig', array(
			'prospection' => $prospection,
		));
	}

	/**
	 * @Route("/crm/prospection/importer/mapping/{id}", name="crm_prospection_importer_mapping")
	 */
	public function prospectionImporterMappingAction(Prospection $prospection)
	{
		//~ exit;
		$request = $this->getRequest();
		$session = $request->getSession();
		$files = $session->get('prospection_import_contacts_filename');
		//~ var_dump($files); exit;
		$output = array();
		//~ $form_mapping = array();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/prospection_contact_import';
		$headers = array();
		$contactData = array();
		$enteteFichierImport = array();
		//~ var_dump($files); exit;
		foreach( $files as $k=>$v )
		{
			// charger PHPEXCEL de choisir le reader adéquat
			$objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$v['nouveau_nom']);
			// chargement du fichier xls/xlsx ou csv
			$objPHPExcel = $objReader->load($path.'/'.$v['nouveau_nom']);

			// chargement des données du fichier
			//~ var_dump($objPHPExcel->getSheetCount());
			//~ exit;
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);
			// changer de delimiter au cas ou le csv n'est pas conforme
			if( get_class($objReader) == 'PHPExcel_Reader_CSV' && !$sheetData[1]['B'] )
			{
				$objReader->setDelimiter(';');
				$objPHPExcel = $objReader->load($path.'/'.$v['nouveau_nom']);
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);
				//~ var_dump($sheetData); exit;
				//~ echo "csv"; exit;
			}
			// chargement du header
			$enteteFichierImport[$k] = $sheetData[1];
			$headers[$k] = array_filter($sheetData[1]);
			// suppression du header et passage des informations à $contactData
			unset($sheetData[1]);
			$contactData[$k] = $sheetData;
			// construction du header
			$col = 'A';
			foreach($headers[$k] as $key => $value){
				$s =  $value.' (col '.$col.')';
				$headers[$k][$key] = $s;
				$col++;
			}

		}

		$session->set('enteteFichierImport', $enteteFichierImport);
		$form_mapping = $this->createForm(new ProspectionImporterMappingType($headers, $files));
		$form_mapping->handleRequest($request);
		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$data = $form_mapping->getData();

			$arr_mapping = array();
			$fields = array();
			//~ var_dump($data);
			foreach( $data as $k=>$v )
			{
				$champ = substr($k, 0, -1);
				$index = substr($k, -1);
				$fields[$index][$champ] = $v;
			}
			$session->set('prospection_champs_mapping', $fields);


			$arr_err_contact = array();
			$arr_err_comptes = array();
			$data_err = array();
			$output_comparaison = array();

            $prospectionInfosRepo =  $em->getRepository('AppBundle:CRM\ProspectionInfos');
            $prospects = $prospectionInfosRepo->findByProspection($prospection);

            foreach ($prospects as $index => $prospect) {
//
                if ($prospect->getContact()->getCompte() !== null && $prospect->getUrl() == null){
                    $url= $prospect->getContact()->getCompte()->getUrl();
                }
                else{
                    $url = $prospect->getUrl();
                }
                if ($prospect->getContact()->getCompte() !== null && $prospect->getCompany() == null){
                    $compte = $prospect->getContact()->getCompte()->getNom();
                }
                else{
                    $compte = $prospect->getCompany();
                }
                $blacklistToday = ($prospect->getBlacklistToday() == new \DateTime(date('Y-m-d'))) ? "yes" : null;

                $obj =  array(
                    "id" => $prospect->getContact()->getId(),
                    "prenom" => $prospect->getContact()->getPrenom(),
                    "nom" => $prospect->getContact()->getNom(),
                    "compte" => $compte,
                    "titre" => $prospect->getContact()->getTitre(),
                    "telephoneFixe" => $prospect->getContact()->getTelephoneFixe(),
                    "telephonePortable" => $prospect->getContact()->getTelephonePortable(),
                    "email" => $prospect->getContact()->getEmail(),
                    "adresse" => $prospect->getContact()->getAdresse(),
                    "ville" => $prospect->getContact()->getVille(),
                    "region" => $prospect->getContact()->getRegion(),
                    "codePostal" => $prospect->getContact()->getCodePostal(),
                    "pays" => $prospect->getContact()->getPays(),
                    "url" => $url,
                    "last_seen" => $prospection->getDateLastOpen(),
                    "date_tentative" => $prospect->getDernierContact(),
                    "blackliste" => $prospect->getBlacklist(),
                    "blacklisteToday" => $blacklistToday,
                    "tentative" => $prospect->getNbreContacts(),
                    "note" => $prospect->getNote(),
                    "onlyProspect" => $prospect->getContact()->getIsOnlyProspect()
                );

                $output[] = $obj;
            }
//
            foreach( $output as $k=>$v )
            {
                $output_comparaison[trim($v['nom']." ".$v['prenom'])] = true;
                $output_comparaison[trim($v['prenom']." ".$v['nom'])] = true;
                $output_comparaison[$v['compte']] = true;
            }
//
            $newContacts = [];

			foreach( $contactData as $k=>$v )
			{
				foreach( $v as $key=>$value )
				{
					if( isset($output_comparaison[$value[$fields[$k]['nom']].' '.$value[$fields[$k]['prenom']]]) )
					{
						$value['err'] = 'Le contact '.$value[$fields[$k]['nom']].' '.$value[$fields[$k]['prenom']].' existe déjà dans la liste';
						$arr_err_contact[$k][] = $value;
						$err = true;
						$data_err[$k][] = $value;
					}
					else if( isset($output_comparaison[$value[$fields[$k]['compte']]]) )
					{
						$value['err'] = 'Le compte '.$value[$fields[$k]['compte']].' existe déjà dans la liste';
						$arr_err_comptes[$k][] = $value;
						$err = true;
						$data_err[$k][] = $value;
					}
					else
					{
						$newContacts[] = array(
						                    'id' => '',
											'nom' => $value[$fields[$k]['nom']],
											'prenom' => $value[$fields[$k]['prenom']],
											'compte' => $value[$fields[$k]['compte']],
											'titre' => '',
											'adresse' => $value[$fields[$k]['adresse']],
											'ville' => '',
											'codePostal' => '',
											'region' => '',
											'pays' => $value[$fields[$k]['activite']],
											'telephoneFixe' => $value[$fields[$k]['telephoneFixe']],
											'telephonePortable' => '',
											'email' => '',
											'url' => '',
                                            'last_seen' => '',
                                            'date_tentative' => '',
                                            'blackliste' => '',
                                            'blacklisteToday' => '',
											'tentative' => '',
											'note' => $value[$fields[$k]['type']]
                        );
						$err = false;
					}
				}
			}

            $prospectionService  = $this->get('appbundle.prospection');


            //enregistrement des contacts comme etant des prospects
            $prospectionService->checkIfProspectList($newContacts, $this->getUser(), $prospection);

			if( count($arr_err_contact) > 0 || count($arr_err_comptes) > 0 )
			{
				$session->set('prospection_erreurs', array('contact' => $arr_err_contact, 'comptes' => $arr_err_comptes, 'data_err' => $data_err));

				//creation du formulaire de validation
				return $this->redirect($this->generateUrl('crm_prospection_importer_validation', array('id'=>$prospection->getId())));

			}
			else
			{
				echo 1;
			}

			exit;
		}

            $k = 0;

		//~ var_dump($session->get('import_contacts_filename'));exit;
		return $this->render('crm/prospection/crm_prospection_importer_mapping.html.twig', array(
				'form' => $form_mapping->createView(),
				'index' => $k,
				'formz' => $form_mapping,
				'prospection' => $prospection
		));

	}


	/**
	 * @Route("/crm/prospection/importer/validation/{id}", name="crm_prospection_importer_validation")
	 */
	public function contactImporterValidationAction(Prospection $prospection)
	{
		//~ echo 1; exit;
		$request = $this->getRequest();
		$session = $request->getSession();
		$arr_err = $session->get('prospection_erreurs');
		$fields = $session->get('prospection_champs_mapping');
		//~ var_dump($fields); exit;
		//~ var_dump($request->files); exit;
		$formBuilder = $this->createFormBuilder();
		//~ var_dump($arr_err); exit;
		$err_comptes = array();
		$err_contacts = array();
		foreach( $arr_err as $entite=>$erreurs )
		{
			$key = 0;
			foreach( $erreurs as $indexFile=>$file )
			{
				//~ var_dump($file); exit;
				switch ($entite){
					case 'contact' :
						foreach( $file as $k=>$v )
						{
							//~ var_dump($v); exit;
							$err_contacts[$entite.'-'.$indexFile.'-'.$key] = strtolower(trim($v[$fields[$indexFile]['nom']].' '.$v[$fields[$indexFile]['prenom']]));
							$formBuilder->add($entite.'-'.$indexFile.'-'.$key.'-radio', 'choice', array(
									'required' => true,
									'mapped' => false,
									'expanded' => true,
									'choices' => array(
											'cancel' => 'Ignorer le contact',
											'add' => 'Ajouter le contact',
									),
									'data' => 'cancel',
									//~ 'data' => $selectedOption

							));
							$key++;
						}
						break;
					case 'comptes' :
						foreach( $file as $k=>$v )
						{
							//~ var_dump($v); //exit;
							//~ $CeCompte = $fields[$erreurs]['compte'];
							$err_comptes[$entite.'-'.$indexFile.'-'.$key] = strtolower(trim($v[$fields[$indexFile]['compte']]));
							$formBuilder->add($entite.'-'.$indexFile.'-'.$key.'-radio', 'choice', array(
									'required' => true,
									'mapped' => false,
									'expanded' => true,
									'choices' => array(
										'cancel' => 'Ignorer le contact',
										'add' => 'Ajouter le contact',
									),
									'data' => 'cancel',

							));
							$key++;
						}
						break;
				}
				$key++;
			}
		}

		$formBuilder->add('submit','submit', array(
				'label' => 'Suite',
				'attr' => array('class' => 'btn btn-success')
		));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {


			$em = $this->getDoctrine()->getManager();


            $output = array();


            $prospectionInfosRepo =  $em->getRepository('AppBundle:CRM\ProspectionInfos');
            $prospects = $prospectionInfosRepo->findByProspection($prospection);

            foreach ($prospects as $index => $prospect) {

                if ($prospect->getContact()->getCompte() !== null && $prospect->getUrl() == null){
                    $url= $prospect->getContact()->getCompte()->getUrl();
                }
                else{
                    $url = $prospect->getUrl();
                }
                if ($prospect->getContact()->getCompte() !== null && $prospect->getCompany() == null){
                    $compte = $prospect->getContact()->getCompte()->getNom();
                }
                else{
                    $compte = $prospect->getCompany();
                }
                $blacklistToday = ($prospect->getBlacklistToday() == new \DateTime(date('Y-m-d'))) ? "yes" : null;

                $obj = array(
                    "id" => $prospect->getContact()->getId(),
                    "prenom" => $prospect->getContact()->getPrenom(),
                    "nom" => $prospect->getContact()->getNom(),
                    "compte" => $compte,
                    "titre" => $prospect->getContact()->getTitre(),
                    "telephoneFixe" => $prospect->getContact()->getTelephoneFixe(),
                    "telephonePortable" => $prospect->getContact()->getTelephonePortable(),
                    "email" => $prospect->getContact()->getEmail(),
                    "adresse" => $prospect->getContact()->getAdresse(),
                    "ville" => $prospect->getContact()->getVille(),
                    "region" => $prospect->getContact()->getRegion(),
                    "codePostal" => $prospect->getContact()->getCodePostal(),
                    "pays" => $prospect->getContact()->getPays(),
                    "url" => $url,
                    "last_seen" => $prospection->getDateLastOpen(),
                    "date_tentative" => $prospect->getDernierContact(),
                    "blackliste" => $prospect->getBlacklist(),
                    "blacklisteToday" => $blacklistToday,
                    "tentative" => $prospect->getNbreContacts(),
                    "note" => $prospect->getNote(),
                    "onlyProspect" => $prospect->getContact()->getIsOnlyProspect()
                );

                $output[] = $obj;
            }


            $newContacts = [];
			foreach( $arr_err['data_err'] as $k=>$v )
			{
				foreach( $v as $index=>$enr )
				{
					if( in_array(strtolower(trim($enr[$fields[$k]['nom']].' '.$enr[$fields[$k]['prenom']])), $err_contacts) )
					{
						$contactKey = array_search(strtolower(trim($enr[$fields[$k]['nom']].' '.$enr[$fields[$k]['prenom']])), $err_contacts);
						if( $form[$contactKey.'-radio']->getData() == 'add' )
						{
							$newContacts[] = array('id' => '',
												'nom' => $enr[$fields[$k]['nom']],
												'prenom' => $enr[$fields[$k]['prenom']],
												'compte' => $enr[$fields[$k]['compte']],
												'titre' => '',
												'adresse' => $enr[$fields[$k]['adresse']],
												'ville' => '',
												'codePostal' => '',
												'region' => '',
												'pays' => '',
												'telephoneFixe' => $enr[$fields[$k]['telephoneFixe']],
												'telephonePortable' => '',
												'email' => '',
												'url' => '',
												'activite' => $enr[$fields[$k]['activite']],
												'type' => $enr[$fields[$k]['type']]);
								//~ $enr[$fields[$k]['email']] = $form[$contactKey.'-name']->getData();
						}

					}



					if( in_array(strtolower(trim($enr[$fields[$k]['compte']])), $err_comptes) )
					{
						$compteKey = array_search(strtolower(trim($enr[$fields[$k]['compte']])), $err_comptes);
						if( $form[$compteKey.'-radio']->getData() == 'add' )
						{
							$newContacts[] = array(
                                'id' => '',
                                'nom' => $enr[$fields[$k]['nom']],
                                'prenom' => $enr[$fields[$k]['prenom']],
                                'compte' => $enr[$fields[$k]['compte']],
                                'titre' => '',
                                'adresse' => $enr[$fields[$k]['adresse']],
                                'ville' => '',
                                'codePostal' => '',
                                'region' => '',
                                'pays' => $enr[$fields[$k]['activite']],
                                'telephoneFixe' => $enr[$fields[$k]['telephoneFixe']],
                                'telephonePortable' => '',
                                'email' => '',
                                'url' => '',
                                'last_seen' => '',
                                'date_tentative' => '',
                                'blackliste' => '',
                                'blacklisteToday' => '',
                                'tentative' => '',
                                'note' => $enr[$fields[$k]['type']]);
						}
					}
				}
			}

            $prospectionService  = $this->get('appbundle.prospection');

            //enregistrement des contacts comme etant des prospects
            $prospectionService->checkIfProspectList($newContacts, $this->getUser(), $prospection);

			$filenames = $session->get('prospection_import_contacts_filename');
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/prospection_contact_import';
			foreach( $filenames as $k=>$v )
			{
				//suppression du fichier temporaire
				unlink($path.'/'.$v['nouveau_nom']);
			}


			echo 1;
			exit;
		}

		return $this->render('crm/prospection/crm_prospection_importer_mapping_validation.html.twig', array(
				'arr_err_comptes' => $arr_err['comptes'],
				'arr_err_contact' => $arr_err['contact'],
				'form' => $form->createView(),
				'prospection' => $prospection
		));
	}
}
