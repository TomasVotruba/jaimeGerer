<?php

namespace AppBundle\Service\CRM;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;


class ContactService extends ContainerAware {

    protected $em;
    protected $requestStack;
    protected $rootDir;

    public function __construct(\Doctrine\ORM\EntityManager $em, RequestStack $requestStack, $rootDir)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->rootDir = $rootDir;
    }

   public function checkContactImportFile($company){

        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $compteRepo = $this->em->getRepository('AppBundle:CRM\Compte');
        $contactRepo = $this->em->getRepository('AppBundle:CRM\Contact');
        $settingsRepo = $this->em->getRepository('AppBundle:Settings');

        $path =  $this->rootDir.'/../web/upload/crm/contact_import';
        $filename = $session->get('validation_import_contact_filename');

        // charger PHPEXCEL de choisir le reader adéquat
        $objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$filename);
        // chargement du fichier xls/xlsx ou csv
        $objPHPExcel = $objReader->load($path.'/'.$filename);
        $arr_data = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);

        $arr_comptes = array(
            'existant' => array(),
            'non-existant' => array(),
        );

        $arr_contacts = array(
            'existant' => array(),
            'non-existant' => array(),
            'doublons' => array(),
            'all' => array(),
            'homonymes' => array(),
        );

        $arr_erreurs = array();
        $numHomonymes = 0;

        //start the loop at 2 to skip the header row
        for($i=2; $i<count($arr_data)+1; $i++){

            $nom = trim($arr_data[$i]['A']);
            $prenom = trim($arr_data[$i]['B']);
            $orga = trim($arr_data[$i]['C']);
            $email = trim($arr_data[$i]['I']);

            if($nom == null && $orga == null){
                break;
            }

            $arr_homonymes = $contactRepo->findByNameAndCompanyNotCompte($prenom, $nom, $company, $orga);
            $numHomonymes+= count($arr_homonymes);

            $arr_contacts['homonymes'][$prenom.' '.$nom.' ('.$orga.')'] = $arr_homonymes;

            if($email){
                if( array_key_exists($email, $arr_contacts['all']) ){
                    $arr_contacts['doublons'][] =  $prenom.' '.$nom.' ('.$orga.')';
                } else {
                    $arr_contacts['all'][$email] = $prenom.' '.$nom.' ('.$orga.')';
                }
            }

            $compte = $compteRepo->findOneBy(array(
                'nom' => $orga,
                'company' => $company
            ));

            if($compte != null){
                //le compte existe

                if( !in_array( $orga, $arr_comptes['existant']) ){
                    $arr_comptes['existant'][$compte->getId()] = $orga;
                }
                
                $contact = $contactRepo->findBy(array(
                    'compte'=> $compte,
                    'prenom' => $prenom,
                    'nom' => $nom
                ));

                if($contact){
                    $arr_contacts['existant'][$contact[0]->getId()] = $prenom.' '.$nom.' ('.$orga.')';
                } else {
                    $arr_contacts['non-existant'][] = $prenom.' '.$nom.' ('.$orga.')';
                }
        
            } else {
                if( !in_array($orga, $arr_comptes['non-existant']) ){
                    $arr_comptes['non-existant'][] = $orga;
                }
                $contact = null;
                if($email){
                    $contact = $contactRepo->findByEmailAndCompany($email, $company);
                }

                if($contact){
                    $arr_contacts['existant'][$contact[0]->getId()] = $prenom.' '.$nom.' ('.$email.')';
                } else {
                    $arr_contacts['non-existant'][] = $prenom.' '.$nom.' ('.$orga.')';
                }

            }

            $reseau = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'RESEAU',
                'valeur' => trim($arr_data[$i]['S'])
            ));
            if($arr_data[$i]['S'] != null && $reseau == null){
                $arr_erreurs[] = 'Ligne '.$i.' : le réseau "'.$arr_data[$i]['S'].'" n\'existe pas';
            }


            $origine = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'ORIGINE',
                'valeur' => trim($arr_data[$i]['T'])
            ));
            if($arr_data[$i]['T'] != null && $origine == null){
                $arr_erreurs[] = 'Ligne '.$i.' : l\'origine "'.$arr_data[$i]['T'].'" n\'existe pas';
            }

            $serviceInteret = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'SERVICE_INTERET',
                'valeur' => trim($arr_data[$i]['U'])
            ));
            if($arr_data[$i]['U'] != null && $serviceInteret == null){
                $arr_erreurs[] = 'Ligne '.$i.' : le service d\'intérêt "'.$arr_data[$i]['U'].'" n\'existe pas';
            }

            $themeInteret = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'THEME_INTERET',
                'valeur' => trim($arr_data[$i]['V'])
            ));
            if($arr_data[$i]['V'] != null && $themeInteret == null){
                $arr_erreurs[] = 'Ligne '.$i.' : le thème d\'intérêt "'.$arr_data[$i]['V'].'" n\'existe pas';
            }

            $secteurActivite = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'SECTEUR',
                'valeur' => trim($arr_data[$i]['W'])
            ));
            if($arr_data[$i]['W'] != null && $secteurActivite == null){
                $arr_erreurs[] = 'Ligne '.$i.' : le secteur d\'activité "'.$arr_data[$i]['W'].'" n\'existe pas';
            }


        } // end for

        return array(
            'contacts' => $arr_contacts,
            'comptes' => $arr_comptes,
            'numHomonymes' => $numHomonymes,
            'erreurs' => $arr_erreurs
        );
    }

    public function importFile($user, $update){
         
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        $compteRepo = $this->em->getRepository('AppBundle:CRM\Compte');
        $contactRepo = $this->em->getRepository('AppBundle:CRM\Contact');
        $settingsRepo = $this->em->getRepository('AppBundle:Settings');

        $path =  $this->rootDir.'/../web/upload/crm/contact_import';
        $filename = $session->get('validation_import_contact_filename');

        // charger PHPEXCEL de choisir le reader adéquat
        $objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$filename);
        // chargement du fichier xls/xlsx ou csv
        $objPHPExcel = $objReader->load($path.'/'.$filename);
        $arr_data = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);

        $company = $user->getCompany();
        $dateCreation = new \DateTime(date('Y-m-d'));

        $arr_results = array(
            'comptes' => array(
                'created' => array(),
                'updated' => array(),
            ),
            'contacts' => array(
                'created' => array(),
                'updated' => array(),
            ),
        );

        //start the loop at 2 to skip the header row
        for($i=2; $i<count($arr_data)+1; $i++){

            $nom = trim($arr_data[$i]['A']);
            $prenom = trim($arr_data[$i]['B']);
            $orga = trim($arr_data[$i]['C']);

            if($nom == null && $prenom == null && $orga == null){
                break;
            }

            $titre = trim($arr_data[$i]['D']);
            $telFixe = trim($arr_data[$i]['E']);
            $telPortable = trim($arr_data[$i]['F']);
            $telAutre = trim($arr_data[$i]['G']);
            $fax = trim($arr_data[$i]['H']);
            $email = trim($arr_data[$i]['I']);
            $email2 = trim($arr_data[$i]['J']);
            $adresse = trim($arr_data[$i]['K']);
            $codePostal = trim($arr_data[$i]['L']);
            $ville = trim($arr_data[$i]['M']);
            $region = trim($arr_data[$i]['N']);
            $pays = trim($arr_data[$i]['O']);
            $description = trim($arr_data[$i]['P']);
            $carteVoeux = trim($arr_data[$i]['Q']);
            $newsletter = trim($arr_data[$i]['R']);
            $reseau = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'RESEAU',
                'valeur' => trim($arr_data[$i]['S'])
            ));
            $origine = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'ORIGINE',
                'valeur' => trim($arr_data[$i]['T'])
            ));
            $serviceInteret = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'SERVICE_INTERET',
                'valeur' => trim($arr_data[$i]['U'])
            ));
            $themeInteret = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'THEME_INTERET',
                'valeur' => trim($arr_data[$i]['V'])
            ));
            $secteurActivite = $settingsRepo->findOneBy(array(
                'company' => $company,
                'module' => 'CRM',
                'parametre' => 'SECTEUR',
                'valeur' => trim($arr_data[$i]['W'])
            ));

            $compte = $compteRepo->findOneBy(array(
                'nom' => $orga,
                'company' => $company
            ));

            if($compte == null){

                //creation nouveau compte
                $compte = new Compte();
                $compte->setCompany($company);
                $compte->setNom($orga);
                $compte->setAdresse($adresse);
                $compte->setCodePostal($codePostal);
                $compte->setVille($ville);
                $compte->setRegion($region);
                $compte->setPays($pays);
                $compte->setSecteurActivite($secteurActivite);
                $compte->setUserCreation($user);
                $compte->setDateCreation($dateCreation);
                $compte->setUserGestion($user);
                $this->em->persist($compte);
                $arr_results['comptes']['created'][] = $compte;

                $contact = null;
                if($email){
                    $contact = $contactRepo->findByEmailAndCompany($email, $company);
                }

                if($contact == null){
                    $contact = new Contact();
                    $contact->setCompte($compte);
                    $contact->setPrenom($prenom);
                    $contact->setNom($nom);
                    $contact->setTitre($titre);
                    $contact->setAdresse($adresse);
                    $contact->setCodePostal($codePostal);
                    $contact->setVille($ville);
                    $contact->setRegion($region);
                    $contact->setPays($pays);
                    $contact->setEmail($email);
                    $contact->setEmail2($email2);
                    $contact->setTelephoneFixe($telFixe);
                    $contact->setTelephonePortable($telPortable);
                    $contact->setTelephoneAutres($telAutre);
                    $contact->setFax($fax);
                    $contact->setDescription($description);
                    if(strtolower($carteVoeux) == "oui"){
                        $contact->setCarteVoeux(true);
                    }
                    if(strtolower($newsletter) == "oui"){
                        $contact->setNewsletter(true);
                    }
                    if($reseau){
                        $contact->setReseau($reseau);
                    }
                    if($origine){
                        $contact->setOrigine($origine);
                    }
                    if($serviceInteret){
                        $contact->addSetting($serviceInteret);
                    }
                    if($themeInteret){
                        $contact->addSetting($themeInteret);
                    }
                    if($secteurActivite){
                        $contact->addSetting($secteurActivite);
                    }
                    $contact->setUserCreation($user);
                    $contact->setDateCreation($dateCreation);
                    $contact->setUserGestion($user);
                    $this->em->persist($contact);
                    $arr_results['contacts']['created'][] = $contact;

                } // end if($contact == null)

            } else if($update == true){

                $compte->setAdresse($adresse);
                $compte->setCodePostal($codePostal);
                $compte->setVille($ville);
                $compte->setRegion($region);
                $compte->setPays($pays);
                $compte->setSecteurActivite($secteurActivite);
                $compte->setUserEdition($user);
                $compte->setDateEdition($dateCreation);
                $this->em->persist($compte);
                $arr_results['comptes']['updated'][] = $compte;

                $contact = $contactRepo->findOneBy(array(
                    'compte'=> $compte,
                    'prenom' => $prenom,
                    'nom' => $nom
                ));

                if($contact){

                    $contact->setTitre($titre);
                    $contact->setAdresse($adresse);
                    $contact->setCodePostal($codePostal);
                    $contact->setVille($ville);
                    $contact->setRegion($region);
                    $contact->setPays($pays);
                    $contact->setEmail($email);
                    $contact->setEmail2($email2);
                    $contact->setTelephoneFixe($telFixe);
                    $contact->setTelephonePortable($telPortable);
                    $contact->setTelephoneAutres($telAutre);
                    $contact->setFax($fax);
                    $contact->setDescription($description);
                    if(strtolower($carteVoeux) == "oui"){
                        $contact->setCarteVoeux(true);
                    } else {
                        $contact->setCarteVoeux(false);
                    }
                    if(strtolower($newsletter) == "oui"){
                        $contact->setNewsletter(true);
                    } else {
                        $contact->setNewsletter(false);
                    }
                    if($reseau){
                        $contact->setReseau($reseau);
                    }
                    if($origine){
                       $contact->setOrigine($origine); 
                    }
                    if($serviceInteret){
                        $contact->addSetting($serviceInteret);
                    }
                    if($themeInteret){
                        $contact->addSetting($themeInteret);
                    }
                    if($secteurActivite){
                        $contact->addSetting($secteurActivite);
                    }
                    $contact->setUserEdition($user);
                    $contact->setDateEdition($dateCreation);
                    $this->em->persist($contact);
                    $arr_results['contacts']['updated'][] = $contact;
                }

            } // end if($compte == null)
           
        } // end for($i=2; $i<count($arr_data)+1; $i++)

        $this->em->flush();
        return $arr_results;

    }
}