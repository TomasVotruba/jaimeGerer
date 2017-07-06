<?php
namespace AppBundle\Tests\Entity\Compta;

use AppBundle\Entity\Compta\Depense;
use AppBundle\Entity\Compta\LigneDepense;
use AppBundle\Entity\Compta\MouvementBancaire;
use AppBundle\Entity\Compta\Rapprochement;

class DepenseTest extends \PHPUnit_Framework_TestCase
{
    private $depense;
    private  $totalHT = 0;
    private  $totalTVA = 0;
    private  $totalTTC = 0;
    private $totalRapproche = 0;

    public function __construct()
    {
      $this->depense = new Depense();

      $min = 1;
      $max = 30000;

      for($i=0; $i<10; $i++){
        $ligne = new LigneDepense();

        //generate random HT price
        $ht = round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
        $ligne->setMontant($ht);
        $this->totalHT+= $ht;

        //generate a 20% tax on the HT price
        $tva = 0;
        if($i<5){
          $tva =  round($ht*0.2, 2);
          $ligne->setTaxe($tva);
          $this->totalTVA+= $tva;
        }

        $ttc = round($ht+$tva, 2);
        $this->totalTTC+= $ttc;

        $this->depense->addLigne($ligne);
      }

      for($i=0; $i<2; $i++){
        $mouvementBancaire = new MouvementBancaire();
        $montant = 0;
        if($i == 0){
          $montant = round($this->totalTTC*0.5, 2);

        } else {
          $montant = round($this->totalTTC*0.25, 2);
        }

        $mouvementBancaire->setMontant($montant);
        $this->totalRapproche+=$montant;
        $rapprochement = new Rapprochement();
        $rapprochement->setMouvementBancaire($mouvementBancaire);

        $this->depense->addRapprochement($rapprochement);
      }

    }

    public function testGetTotalHT()
    {
        $this->assertEquals($this->totalHT, $this->depense->getTotalHt());
    }

    public function testGetTotalTVA()
    {
        $this->assertEquals($this->totalTVA, $this->depense->getTotalTVA());
    }

    public function testGetTotalTTC()
    {
        $this->assertEquals($this->totalTTC, $this->depense->getTotalTTC());
    }

    public function testGetTotaux()
    {
        $arr_totaux = $this->depense->getTotaux();

        $this->assertEquals($this->totalHT, $arr_totaux['HT']);
        $this->assertEquals($this->totalTVA, $arr_totaux['TVA']);
        $this->assertEquals($this->totalTTC, $arr_totaux['TTC']);
    }

    public function testGetTotalRapproche()
    {
        $this->assertEquals(-$this->totalRapproche, $this->depense->getTotalRapproche());
    }
}
