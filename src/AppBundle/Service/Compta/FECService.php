<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\UtilsService;


class FECService extends ContainerAware {

    protected $em;
  	protected $rootDir;
    protected $utilsService;
    protected $separator;

  	public function __construct(\Doctrine\ORM\EntityManager $em, $rootDir, UtilsService $utilsService)
  	{
   		$this->em = $em;
        $this->rootDir = $rootDir;
        $this->utilsService = $utilsService;
        $this->separator = '|';
  	}

    /**
     * Create FEC file
     * Info from http://www.fidulorraine.fr/documents/publications/fec.pdf
     * 
     **/
    public function createFECFile($company, $year){

        ini_set('memory_limit', '2048M');

        $compteBancaireRepo = $this->em->getRepository('AppBundle:Compta\CompteBancaire');
        $compteComptableRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');

        $all_comptesBancaires = $compteBancaireRepo->findByCompany($company);
        $arr_comptesBancaires = array();
        foreach($all_comptesBancaires as $cb){
            $arr_comptesBancaires[$cb->getNom()] = $cb->getNomComplet();
        }

        $arr_lignes = $this->getFECData($company, $year);
       
        $path = $this->rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'compta'.DIRECTORY_SEPARATOR.$company->getId().DIRECTORY_SEPARATOR.'fec';
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
         /* Chaque FEC doit être nommé selon la nomenclature suivante : SirenFECAAAAMMJJ. */
        $filename = $path.DIRECTORY_SEPARATOR.$company->getSiren().'FEC'.$year.'1231.txt';

        if(file_exists($filename)){
            unlink($filename);
        }

        $string = 'JournalCode|JournalLib|EcritureNum|EcritureDate|CompteNum|CompteLib|CompAuxNum|CompAuxLib|PieceRef|PieceDate|EcritureLib|Debit|Credit|EcritureLet|DateLet|ValidDate|Montantdevise|Idevise';
        $string.=PHP_EOL;

        file_put_contents ( $filename , utf8_encode($string) );

        foreach($arr_lignes as $ligne){

            $string = "";

            /* 
                CHAMP 1 : CODE JOURNAL
                Le code journal fait référence à la codifi cation utilisée dans le logiciel comptable pour
                référencer les différents journaux en cas de système centralisateur et de journaux auxiliaires.
                Ces codes peuvent correspondre à des chiffres, des lettres ou une combinaison des deux
            */
            $string.= $ligne->getCodeJournal();
            $string.=$this->separator;
            
            /*
                CHAMP 2 : LIBELLÉ JOURNAL
                Le libellé journal fait référence au nom complet du journal utilisé dans le logiciel. 
                Notons que le document descriptif doit comprendre un tableau de correspondance des codes
                journaux et des libellés.
            */
            $libelleJournal = '';
            switch($ligne->getCodeJournal()){
                
                case 'VE':
                    $libelleJournal = 'Journal des ventes';
                    break;

                case 'AC':
                    $libelleJournal = 'Journal des achats';
                    break;

                case 'OD':
                    $libelleJournal = 'Journal des opérations diverses';
                    break;

                default:

                    if(array_key_exists($ligne->getCodeJournal(), $arr_comptesBancaires)){
                        $libelleJournal = $arr_comptesBancaires[$ligne->getCodeJournal()];
                    } else {
                        $libelleJournal = 'Journal de banque';
                    }
                    
                    break;
            } 
            $string.= $libelleJournal;
            $string.=$this->separator;

            /*
                CHAMP 3 : NUMÉROTATION DES ÉCRITURES
                Les écritures doivent être numérotées chronologiquement de manière croissante, sans rupture
                ni inversion dans la séquence. 
            */
            $string.=$ligne->getNumEcriture();
            $string.=$this->separator;

            /*
                CHAMP 4 : DATE DE COMPTABILISATION
                Pour l’administration, la date de comptabilisation de l’écriture comptable correspond à la
                date à laquelle l’enregistrement comptable de l’opération a été porté au débit ou au crédit
                du compte. 
            */
            $string.= $ligne->getDate()->format('Ymd');
            $string.=$this->separator;

            /*
                CHAMP 5 : NUMÉRO DE COMPTE
                Le champ « CompteNum » doit être rempli par les numéros de compte (au moins les trois
                premiers caractères) du PCG, utilisés pour chaque ligne d’écriture comptable.
                Lorsque l’entreprise décline le compte du PCG en subdivisions qui lui sont propres, 
                celles-ci doivent figurer dans le champ « numéro de compte ». 
            */
            $shortNum = substr($ligne->getCompteComptable()->getNum(), 0, 3);
            $string.= str_pad($shortNum, 8, '0');
            $string.=$this->separator;

            /*
                CHAMP 6 : LIBELLÉ DU COMPTE
                Il faut reprendre l’intitulé complet du compte du PCG (ou du plan comptable particulier).
                Néanmoins, selon l’administration, les libellés utilisés au sein de l’entreprise ou ceux
                correspondant à des subdivisions plus détaillées du plan comptable français doivent figurer
                dans le fichier remis à l’administration et ne pas être remplacés par un libellé générique
            */
            $compteComptable = $compteComptableRepo->findOneBy(array(
                'company' => $company,
                'num' => $shortNum
            ));
            if($compteComptable == null){

                $compteComptable = $compteComptableRepo->findOneBy(array(
                     'company' => $company,
                     'num' => str_pad($shortNum, 8, '0')
                 ));

                if(!$compteComptable){
                    var_dump($shortNum);
                }

            }

            $string.= $compteComptable->getNom();
            $string.=$this->separator;

            /*
                CHAMPS 7 ET 8 : NUMÉRO ET LIBELLÉ DE COMPTE AUXILIAIRE
                Le numéro de compte auxiliaire correspond à la codifi cation des comptes de tiers utilisée au
                sein de l’entreprise. Le libellé de compte auxiliaire reprend la désignation littérale du tiers.
            */
            
            $string.= $ligne->getCompteComptable()->getNum();
            $string.=$this->separator;

            $string.= $ligne->getCompteComptable()->getNom();
            $string.=$this->separator;

            /*
                CHAMP 9 : RÉFÉRENCE DE LA PIÈCE JUSTIFICATIVE
                Le caractère sincère, régulier et probant de la comptabilité n’est notamment acquis que si
                l’on peut :
                – associer à chaque écriture la référence à la pièce justificative qui l’appuie (PCG art. 420-2) ;
                – assurer la permanence du chemin de révision entre les pièces justificatives et la
                comptabilité (PCG art. 410-3), afin de pouvoir faire le lien avec les pièces justificatives qui
                motivent les écritures comptables (PCG art. 420-3).
                C’est pourquoi toutes les écritures devraient en principe faire référence à une pièce
                justificative, même si celle-ci est d’origine interne (calcul de provision par exemple). Ce lien
                est établi soit en utilisant la référence figurant sur les pièces justificatives, soit grâce à une
                numérotation séquentielle des pièces comptables dans le système, qui est aussi apposée
                sur la pièce.
                Les pièces justificatives, obligatoirement datées (PCG art. 420-3), sont classées dans un ordre
                qui doit être défi ni dans le document décrivant les procédures et l’organisation comptables
                (c. com. art. R. 123-174, al. 4 ; PCG art. 420-3). La numérotation et le plan de classement doivent
                permettre de les retrouver. Ce document précisera aussi les modalités (supports) et les
                lieux de classement. L’entreprise est libre de choisir sa méthode de classement des pièces :
                chronologique, alphabétique, numérique ou par nature.
                L’administration admet cependant que certaines écritures puissent ne pas avoir de référence
                de pièce (par exemple, pour les écritures d’à nouveau). En ce cas, ce champ doit néanmoins
                être rempli par une valeur conventionnelle défi nie par l’entreprise. Celle-ci sera précisée
                dans le descriptif 
            */
            $string.= $ligne->getPiece();
            if($ligne->getCodeJournal() != 'OD'){
                $string.=' '.$ligne->getAnalytique();
            }
            $string.=$this->separator;  

            /*
                CHAMP 10 : DATE DE LA PIÈCE JUSTIFICATIVE
                La date de la pièce justificative correspond à la date à laquelle le justificatif est enregistré en
                comptabilité ou à la date figurant sur les pièces justificatives reçues ou émises.
            */
            $date = $ligne->getDatePiece();
            if($date){
                $date = $date->format('Ymd');
            }
            $string.= $date;
            $string.=$this->separator;

            /*
                CHAMP 11 : LIBELLÉ DE L’ÉCRITURE COMPTABLE
                Le libellé de l’écriture comptable correspond à l’identification littérale du motif de l’écriture
                comptable.
            */
            $libelle = $this->purify($ligne->getLibelle());
            $string.= $libelle;
            $string.=$this->separator;

            /*
                CHAMPS 12 ET 13 : DÉBIT ET CRÉDIT
            */
            if($ligne->getDebit() == null){
                $string.= '0';
            } else {
                $string.= number_format($ligne->getDebit(),2,',','');
            }
            $string.=$this->separator;

            if($ligne->getCredit() == null){
                $string.= '0';
            } else {
                $string.= number_format($ligne->getCredit(),2,',','');
            }
            $string.=$this->separator;

            /*
                CHAMPS 14 ET 15 : LETTRAGE DE L’ÉCRITURE COMPTABLE ET DATE DE LETTRAGE
                Le lettrage de l’écriture fait référence au repère utilisé dans le système comptable pour
                apparier deux écritures (règlement-facture).
                La date de lettrage de l’écriture correspond à la date à laquelle l’opération de lettrage a été
                validée dans le système comptable.
            */
            $string.= $ligne->getLettrage();
            $string.=$this->separator; 
            $string.=$this->separator;  

            /*
                CHAMP 16 : DATE DE VALIDATION
                Il s’agit de la date à laquelle l’enregistrement comptable de l’opération a été porté au
                débit ou au crédit du compte, c’est-à-dire porté dans le livre-journal sans possibilité de
                modification ou suppression ultérieure.
            */
            $string.= date('Ymd');
            $string.=$this->separator;

            /*
                CHAMPS 17 ET 18 : MONTANT EN DEVISES ET IDENTIFICATION DE LA DEVISE
                Pour les prestations réalisées dans des pays tiers, le montant en devises étrangères
                figurant sur la pièce justificative devra être indiqué dans la zone « Montantdevise ». Si seul
                le montant en devise étrangère est enregistré en comptabilité, les champs « débit » et
                « crédit » seront alors remplis par la valeur zéro. Ainsi, le montant de la devise correspond
                à un montant signé, exprimé en devise, porté au crédit ou au débit du compte.
                L’identification de la devise correspond à la devise utilisée. L’information demandée peut
                faire référence à une codification (par exemple : code 01 : euro ; code 02 : dollar américain ;
                code 03 : yen ; etc.). Ces codes peuvent correspondre à des chiffres, des lettres ou une
                combinaison des deux
            */
            $string.=$this->separator;


            $string.=PHP_EOL;

            file_put_contents ( $filename , $string, FILE_APPEND );
        }


        return $filename;
    }

     /**
     * Get all data sorted by creation date
     **/
    public function getFECData($company, $year){
        
        $journalVenteRepo = $this->em->getRepository('AppBundle:Compta\JournalVente');
        $journalAchatRepo = $this->em->getRepository('AppBundle:Compta\JournalAchat');
        $journalBanqueRepo = $this->em->getRepository('AppBundle:Compta\JournalBanque');
        $journalODRepo = $this->em->getRepository('AppBundle:Compta\OperationDiverse');

        $arr_journalVente = $journalVenteRepo->findJournalEntier($company, $year);
        $arr_journalAchat = $journalAchatRepo->findJournalEntier($company, $year);
        $arr_journalBanque = $journalBanqueRepo->findByCompanyAndYear($company, $year);
        $arr_journalOD = $journalODRepo->findJournalEntier($company, $year);

        $arr_lignes = array_merge($arr_journalVente, $arr_journalAchat, $arr_journalBanque, $arr_journalOD);
        usort($arr_lignes, array($this, 'orderByDateCreation'));

        return $arr_lignes;
    }

    function orderByDateCreation($a, $b){
        if ($a->getDateCreation() == $b->getDateCreation()) {
            if($a->getPieceId() == $a->getPieceId()){
                return 0;
            }
            return ($a->getPieceId() < $b->getPieceId()) ? -1 : 1;
        }
        return ($a->getDateCreation() < $b->getDateCreation()) ? -1 : 1;
    }

    /**
     * Remove carriage returns and pipes from $string as those are reserved characters
     **/
    function purify($string){

        $purifiedString = preg_replace('/[\r\n]+/',' - ', $string);
        $purifiedString = str_replace('|', ' ', $purifiedString);

        return $purifiedString;
    }

    /**
     * Create FEC Description file
     * Info from http://www.fidulorraine.fr/documents/publications/fec.pdf
     * 
     **/
    public function createFECDescFile($company, $year){

        $path = $this->rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'compta'.DIRECTORY_SEPARATOR.$company->getId().DIRECTORY_SEPARATOR.'fec';
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
        $filename = $path.DIRECTORY_SEPARATOR.$company->getSiren().'FEC'.$year.'1231_desc.txt';

        if(file_exists($filename)){
            unlink($filename);
        }

        $string = 'Fichier de description des données particulières'.PHP_EOL;
        $string.= PHP_EOL;

        $string.= '* Caractéristiques techniques :'.PHP_EOL;
        $string.= 'encodage = UTF-8'.PHP_EOL;
        $string.= 'séparateur de zone = '.$this->separator.$PHP_EOL;
        $string.= 'séparateur d\'enregistrement = retour chariot'.PHP_EOL;
        $string.= 'Longueur des enregistrements variable'.PHP_EOL;
        $string.= PHP_EOL;

        $string.= '* Nom, Nature et Signification de chaque zone :'.PHP_EOL;
        $string.= 'JournalCode Alphanumérique  Le code journal de l\'écriture comptable'.PHP_EOL;
        $string.= 'JournalLib  Alphanumérique  Le libellé journal de l\'écriture comptable'.PHP_EOL;
        $string.= 'EcritureNum Alphanumérique  Le numéro sur une séquence continue de l\'écriture comptable'.PHP_EOL;
        $string.= 'EcritureDate    Date    La date de l\'écriture comptable'.PHP_EOL;
        $string.= 'CompteNum   Alphanumérique  Le numéro de compte, dont les 3 premiers caractères doivent correspondre à des chiffres respectant les normes du plan comptable français'.PHP_EOL;
        $string.= 'CompteLib   Alphanumérique  Le libellé de compte, conformément à la nomenclature du plan comptable français'.PHP_EOL;
        $string.= 'CompAuxNum  Alphanumérique  Le numéro de compte auxiliaire (à blanc si non utilisé)'.PHP_EOL;
        $string.= 'CompAuxLib  Alphanumérique  Le libellé de compte auxiliaire (à blanc si non utilisé)'.PHP_EOL;
        $string.= 'PieceRef    Alphanumérique  La référence de la pièce justificative'.PHP_EOL;
        $string.= 'PieceDate   Date    La date de la pièce justificative'.PHP_EOL;
        $string.= 'EcritureLib Alphanumérique  Le libellé de l\'écriture comptable'.PHP_EOL;
        $string.= 'Debit   Numérique   Le montant au débit'.PHP_EOL;
        $string.= 'Credit  Numérique   Le montant au crédit'.PHP_EOL;
        $string.= 'EcritureLet Alphanumérique  Le lettrage de l\'écriture comptable (à blanc si non utilisé)'.PHP_EOL;
        $string.= 'DateLet Date    La date de lettrage (à blanc si non utilisé)'.PHP_EOL;
        $string.= 'ValidDate   Date    La date de validation de l\'écriture comptable'.PHP_EOL;
        $string.= 'Montantdevise   Numérique   Le montant en devise (à blanc si non utilisé)'.PHP_EOL;
        $string.= 'Idevise Alphanumérique  L\'identifiant de la devise (à blanc si non utilisé)'.PHP_EOL;
        $string.= PHP_EOL;

        $string.= '* Tables de correspondance :'.PHP_EOL;
        $string.= PHP_EOL;

        file_put_contents ( $filename , $string, FILE_APPEND );

        return $filename;


    }

}
