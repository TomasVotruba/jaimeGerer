<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Compta\MouvementBancaire;

class ReleveBancaireService extends ContainerAware {

  protected $em;
  protected $path;

  public function __construct(\Doctrine\ORM\EntityManager $em, $rootDir)
  {
    $this->em = $em;
    $this->path = $rootDir.'/../web/upload/compta/releve_bancaire/';
  }


  public function parseReleveCSV($colDate, $colLibelle, $colDebit, $colCredit, $dateFormat, $filename, $compteBancaire){

  	$total = 0;
  	$arr_mouvements = array();

  	//parsing du CSV
	$csv = new \parseCSV();
	$csv->delimiter = ";";
	$csv->encoding('ISO-8859-1', 'UTF-8');
	$csv->parse($this->path.$filename);

	//parsing ligne par ligne
	foreach($csv->data as $data){

		if($data[$colDate] == "" || $data[$colDate] == null){
			continue;
		}

		if(array_key_exists($colLibelle, $data) && array_key_exists($colCredit, $data) && array_key_exists($colDebit, $data) && array_key_exists($colDate, $data) ){

			//creation et hydratation du mouvement bancaire
			$mouvement = new MouvementBancaire();
			$mouvement->setCompteBancaire($compteBancaire);
			$mouvement->setLibelle($data[$colLibelle]);

			$date = \DateTime::createFromFormat($dateFormat, $data[$colDate]);
			$mouvement->setDate($date);

			if($data[$colCredit] > 0){
				$montant = $data[$colCredit];
				$montant = str_replace(',','.',$montant);
				$montant = preg_replace('/\s+/u', '', $montant);

			} else {
				$montant = $data[$colDebit];
				$montant = str_replace(',','.',$montant);
				$montant = preg_replace('/\s+/u', '', $montant);
				if($montant > 0){
					$montant= -$montant;
				}
			}

			$mouvement->setMontant($montant);
			$arr_mouvements[] = $mouvement;
			$total+=$montant;
		}

	}

	return array(
		'total' => $total,
		'arr_mouvements' => $arr_mouvements
	);

  }

  

  


}
