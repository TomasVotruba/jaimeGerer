<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Options\VAxis;

class ChartService extends ContainerAware {

   /**
   * Crée le graphique du taux de transformation des opportunites
   *
   * @return PieChart
   */
    public function opportuniteTauxTransformationPieChart($arr_data)
    {
        $pieChart = new PieChart();
        //set data
        $pieChart->getData()->setArrayToDataTable(
            [['Etat', 'Nombre d\'opportunités'],
             ['Gagnées', $arr_data['won']],
             ['Perdues', $arr_data['lost']],
            ]
        );

        //chart area
        $pieChart->getOptions()->getChartArea()
          ->setHeight('90%')
          ->setWidth('100%');

        $pieChart->getOptions()->setHeight(250)
                               ->setWidth(250);

        //legend
        $pieChart->getOptions()->getLegend()->setPosition('bottom');

        //other options
        $pieChart->getOptions()
          ->setPieHole(0.4)
          ->setColors(array('#5cb85c', '#c9302c'));

        return $pieChart;
    }

    /**
   * Crée le graphique du montant des opportunités par analytique (stacked public/privé)
   *
   * @return ColumnChart
   */
    public function actionsCoAnalytique($arr_data)
    {
        $colChart = new ColumnChart();

        $arrayDataTable = array(
            ['Type', 'Public', 'Privé' ]
        );
        foreach($arr_data as $analytique => $data){
            if($analytique != "FG" ){
                $arrayDataTable[] = [
                    $analytique, 
                    round($data['public']), 
                    round($data['prive'])
                ];
            }
        }

        $colChart->getData()->setArrayToDataTable($arrayDataTable);
        $colChart->getOptions()->setColors(array('#ec741b'));
        $colChart->getOptions()->setIsStacked(true);

        /*$colChart->getOptions()->setHeight(250)
                               ->setWidth(400);*/

        return $colChart;
    }

     /**
   * Crée le graphique du montant des opportunités rhône-alpes / hors rhône-alpes
   *
   * @return ColumnChart
   */
    public function actionsCoRhoneAlpes($arr_data)
    {
        $colChart = new ColumnChart();

        $arrayDataTable = array(
            ['Lieu', 'Public', 'Privé' ]
        );
        foreach($arr_data as $region => $data){
            
            $arrayDataTable[] = [
                $region, 
                round($data['public']), 
                round($data['prive'])
            ];
        }

        $colChart->getData()->setArrayToDataTable($arrayDataTable);
        $colChart->getOptions()->setColors(array('#ec741b'));
        $colChart->getOptions()->setIsStacked(true);

       /* $colChart->getOptions()->setHeight(250)
                               ->setWidth(400);*/

        return $colChart;
    }

     /**
    * Crée le graphique du montant du chiffre d'affaire par analytique (stacked public/privé)
    *
    * @return ColumnChart
    */
     public function caAnalytique($arr_data)
     {
         $colChart = new ColumnChart();

         $arrayDataTable = array(
             ['Type', 'Public', 'Privé' ]
         );
         foreach($arr_data as $analytique => $data){
             if($analytique != "FG" ){
                 $arrayDataTable[] = [
                     $analytique, 
                     round($data['public']), 
                     round($data['prive'])
                 ];
             }
         }

         $colChart->getData()->setArrayToDataTable($arrayDataTable);
         $colChart->getOptions()->setColors(array('#ec741b'));
         $colChart->getOptions()->setIsStacked(true);

         return $colChart;
    }

      /**
   * Crée le graphique du montant du chiffre d'affaire rhône-alpes / hors rhône-alpes
   *
   * @return ColumnChart
   */
    public function caRhoneAlpes($arr_data)
    {
        $colChart = new ColumnChart();

        $arrayDataTable = array(
            ['Lieu', 'Public', 'Privé' ]
        );
        foreach($arr_data as $region => $data){
            
            $arrayDataTable[] = [
                $region, 
                round($data['public']), 
                round($data['prive'])
            ];
        }

        $colChart->getData()->setArrayToDataTable($arrayDataTable);
        $colChart->getOptions()->setColors(array('#ec741b'));
        $colChart->getOptions()->setIsStacked(true);

        return $colChart;
    }

}
