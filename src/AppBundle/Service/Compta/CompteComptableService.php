<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Compta\CompteComptable;
use AppBundle\Entity\CRM\Compte;

class CompteComptableService extends ContainerAware {

    protected $em;
    protected $utilsService;

    public function __construct(\Doctrine\ORM\EntityManager $em, $utilsService)
    {
        $this->em = $em;
        $this->utilsService = $utilsService;
    }

    public function createCompteComptable($company, $num, $nom){
        $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
        $cc = $ccRepo->findBy(array(
            'company' => $company,
            'num' => $num
        ));
        if($cc){
            throw new \Exception('Le compte '.$num.' existe déjà.');
        }

        $cc = new CompteComptable();
    	$cc->setCompany($company);
        $cc->setNum($num);
        $cc->setNom($nom);

        return $cc;
    }

    /**
     * @Route("/compta/compte/create-compte-comptable-client/{compte}", 
     *     name="create_compte_comptable_client", 
     *     options={"expose"=true}
     *  )
     */
    public function createCompteComptableClient(Compte $compte){

        $compteComptableRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');

        //find array of existing nums for this company
        $arr_nums = $compteComptableRepo->findAllNumForCompany($compte->getCompany());
        $arr_existings_nums = array();
        foreach($arr_nums as $arr){
            $arr_existings_nums[] = $arr['num'];
        }

        $nbChars = 3;
        $baseNum = '411';
        $nomCompte = $compte->getNom();
        $num = $this->_createNum($baseNum, $nomCompte, $nbChars);
        
        //max 8 characters (3 for baseNum, 5 for the name - ex : 411CLIEN)
        while(in_array($num, $arr_existings_nums) && $nbChars<=5) {
            $nbChars++;
            $num = $this->_createNum($baseNum, $nomCompte, $nbChars);
        }

        if(in_array($num, $arr_existings_nums)){
            throw new \Exception('Impossible de créer automatiquement un numéro pour '.$compte->getNom().'.');
        }

        $compteComptable = new CompteComptable();
        $compteComptable->setNom($nomCompte);
        $compteComptable->setCompany($compte->getCompany());
        $compteComptable->setNum($num);
        $this->em->persist($compteComptable);
        $this->em->flush();

        // if($baseNum == '401'){
        //     $compteTVA = $compteComptableRepo->findOneBy(array(
        //         'num' => '44566000',
        //         'company' => $this->getUser()->getCompany()
        //     ));
        //     $compteComptable->setCompteTVA($compteTVA);
        // }
        return $compteComptable;
    }

    private function _createNum($baseNum, $nomCompte, $nbChars){
        $nom = substr($nomCompte,0,$nbChars);
        $nom = $this->utilsService->removeSpecialChars($nom);
        $nom = mb_strtoupper($nom, 'UTF-8');
        $num = $baseNum.$nom;
        return $num;
    }

    public function getCompteAttente($company){
        $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
        $cc = $ccRepo->findOneBy(array(
            'company' => $company,
            'num' => 471
        ));

        if(!$cc){
            $cc = new CompteComptable();
            $cc->setNom("Compte d'attente");
            $cc->setCompany($company);
            $cc->setNum('471');
            $this->em->persist($cc);
            $this->em->flush();
        }

        return $cc;
    }


}
