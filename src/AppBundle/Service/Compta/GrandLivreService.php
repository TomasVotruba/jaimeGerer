<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

class GrandLivreService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function creerGrandLivre($company, $startDate, $endDate){
    $arr_grand_livre = array();

    $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
    $arr_cc = $ccRepo->findBy(array(
      'company' => $company
    ), array(
      'num' => 'ASC'
    ));

    foreach($arr_cc as $cc){
      $arr_grand_livre[] = array(
        'cc' => $cc,
        'lignes' => $this->creerGrandLignePourCompte($cc, $startDate, $endDate)
      );
    }

    return $arr_grand_livre;
  }

  public function exportGrandLivre($company, $startDate, $endDate){

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->setTitle('Grand Livre');

    // header row
    $arr_header = array(
      'Date',
      'Journal',
      'Compte',
      'N° pièce',
      'Libellé',
      'Débit',
      'Crédit'
    );
    $row = 1;
    $col = 'A';
    foreach($arr_header as $header){
        $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $header);
        $col++;
    }
    $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
    $row++;

    $arr_grand_livre = $this->creerGrandLivre($company, $startDate, $endDate);
    foreach($arr_grand_livre as $arr){
      $col = 'A';
      $cc = $arr['cc'];

      $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $cc->__toString());
      $objPHPExcel->getActiveSheet()->getStyle($col.$row)->getFont()->setBold(true);

      foreach($arr['lignes'] as $ligne){
        $row++;
        $col = 'A';
        $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDate()->format('d/m/Y'));
        $col++;
        $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCodeJournal());
        $col++;
        $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getPiece());
        $col++;
        $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getLibelle());
        $col++;
        if($ligne->getDebit() > 0){
            $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDebit());
        }
        $col++;
        if($ligne->getCredit() > 0){
          $objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCredit());
        }
      }

      $row++;
    }

    foreach(range('A','G') as $columnID) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
        ->setAutoSize(true);
    }

    return $objPHPExcel;
  }

  private function creerGrandLignePourCompte($compteComptable, $startDate, $endDate){
    $arr_lignes = array();

    //lignes du journal de ventes pour le compte $compteComptable
    $repoJournalVente = $this->em->getRepository('AppBundle:Compta\JournalVente');
    $arr_journal_vente = $repoJournalVente->findByCompteForCompany(
      $compteComptable,
      $compteComptable->getCompany(),
      $startDate,
      $endDate
    );

    //lignes du journal d'achats pour le compte $compteComptable
    $repoJournalAchat = $this->em->getRepository('AppBundle:Compta\JournalAchat');
    $arr_journal_achat = $repoJournalAchat->findByCompteForCompany(
      $compteComptable,
      $compteComptable->getCompany(),
      $startDate,
      $endDate
    );

    //lignes du journal de banque pour le compte $compteComptable
    $repoJournalBanque = $this->em->getRepository('AppBundle:Compta\JournalBanque');
    $arr_journal_banque = $repoJournalBanque->findByCompteForCompany(
      $compteComptable,
      $compteComptable->getCompany(),
      $startDate,
      $endDate
    );

    //lignes des opérations diverses pour le compte $compteComptable
    $repoOperationDiverse = $this->em->getRepository('AppBundle:Compta\OperationDiverse');
    $arr_operation_diverse = $repoOperationDiverse->findByCompteForCompany(
      $compteComptable,
      $compteComptable->getCompany(),
      $startDate,
      $endDate
    );

    //regroupement dans 1 seul array
    $arr_lignes = array_merge($arr_journal_vente, $arr_journal_achat, $arr_journal_banque, $arr_operation_diverse );
    usort($arr_lignes, array($this, 'orderByDate'));

    return $arr_lignes;

  }

  function orderByDate($a, $b)
  {
    if ($a->getDate() == $b->getDate()) {
     return 0;
   }
   return ($a->getDate() < $b->getDate()) ? -1 : 1;
  }


}
