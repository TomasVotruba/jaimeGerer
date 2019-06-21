<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\CandlestickChart;
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

     /**
    * Crée le graphique du temps commercial des opportunités par analytique
    *
    * @return ColumnChart
    */
     public function actionsCoTempsCommercialeAnalytique($arr_data)
     {
         $colChart = new ColumnChart();

         $arrayDataTable = array(
             ['Type', 'Temps moyen par action commerciale (en heure)' ]
         );
         foreach($arr_data as $analytique => $data){
             if($analytique != "FG" ){
                 $arrayDataTable[] = [
                     $analytique, 
                     $data, 
                     
                 ];
             }
        }

         $colChart->getData()->setArrayToDataTable($arrayDataTable);
         $colChart->getOptions()->setColors(array('#ec741b'));
         $colChart->getOptions()->getLegend()->setPosition('top');

         return $colChart;
     }

     /**
    * Crée le graphique du temps commercial des opportunités par AO/pas AO
    *
    * @return ColumnChart
    */
     public function actionsCoTempsCommercialeAO($arr_data)
     {
         $colChart = new ColumnChart();

         $arrayDataTable = array(
             ['Type', 'Temps moyen par action commerciale (en heure)' ]
         );
         foreach($arr_data as $type => $data){
            $arrayDataTable[] = [
                $type, 
                $data, 
            ];
        }

         $colChart->getData()->setArrayToDataTable($arrayDataTable);
         $colChart->getOptions()->setColors(array('#ec741b'));
         $colChart->getOptions()->getLegend()->setPosition('top');

         return $colChart;
    }

    /**
    * Crée le graphique de la repartition du temps commercial par analytique
    *
    * @return PieChart
    */
     public function actionsCoTempsCommercialeAnalytiqueRepartition($arr_data)
     {
         $pieChart = new PieChart();

         //set data
         $arrayDataTable = [
            ['Type', 'Temps passé'],
         ];
        foreach($arr_data as $type => $data){
            $arrayDataTable[] = [
                $type, 
                $data, 
            ];
        }
        $pieChart->getData()->setArrayToDataTable($arrayDataTable);
            

         //chart area
         $pieChart->getOptions()->getChartArea()
           ->setHeight('90%')
           ->setWidth('100%');

         $pieChart->getOptions()->setHeight(250)
                                ->setWidth(250);

         //legend
        // $pieChart->getOptions()->getLegend()->setPosition('bottom');

         //other options
         $pieChart->getOptions()
           ->setPieHole(0.4)
           ->setColors(array('#ec741b', '#00a7d6', '#e32283', '#bfce00', '#a03488', '9e928f'));

         return $pieChart;
    }


    /**
    * Crée le graphique de la repartition du temps commercial par AO/pas AO
    *
    * @return PieChart
    */
     public function actionsCoTempsCommercialeAORepartition($arr_data)
     {
         $pieChart = new PieChart();

         //set data
         $arrayDataTable = [
            ['Type', 'Temps passé'],
         ];
        foreach($arr_data as $type => $data){
            $arrayDataTable[] = [
                $type, 
                $data, 
            ];
        }
        $pieChart->getData()->setArrayToDataTable($arrayDataTable);
            

         //chart area
         $pieChart->getOptions()->getChartArea()
           ->setHeight('90%')
           ->setWidth('100%');

         $pieChart->getOptions()->setHeight(250)
                                ->setWidth(250);

         //legend
        // $pieChart->getOptions()->getLegend()->setPosition('bottom');

         //other options
         $pieChart->getOptions()
           ->setPieHole(0.4)
           ->setColors(array('#ec741b', '#00a7d6', '#e32283', '#bfce00', '#a03488', '9e928f'));

         return $pieChart;
    }
    
     /**
    * Crée le graphique du temps commercial des opportunités par privé ou public
    *
    * @return ColumnChart
    */
     public function actionsCoTempsCommercialePrivePublic($arr_data)
     {
         $colChart = new ColumnChart();

         $arrayDataTable = array(
             ['Type', 'Temps moyen par action commerciale (en heure)' ]
         );
         foreach($arr_data as $type => $data){
            $arrayDataTable[] = [
                $type, 
                $data, 
            ];
        }

         $colChart->getData()->setArrayToDataTable($arrayDataTable);
         $colChart->getOptions()->setColors(array('#ec741b'));
         $colChart->getOptions()->getLegend()->setPosition('top');

         return $colChart;
    }

    /**
    * Crée le graphique de la repartition du temps commercial privé/public
    *
    * @return PieChart
    */
     public function actionsCoTempsCommercialePrivePublicRepartition($arr_data)
     {
         $pieChart = new PieChart();

         //set data
         $arrayDataTable = [
            ['Type', 'Temps passé'],
         ];
        foreach($arr_data as $type => $data){
            $arrayDataTable[] = [
                $type, 
                $data, 
            ];
        }
        $pieChart->getData()->setArrayToDataTable($arrayDataTable);
            

         //chart area
         $pieChart->getOptions()->getChartArea()
           ->setHeight('90%')
           ->setWidth('100%');

         $pieChart->getOptions()->setHeight(250)
                                ->setWidth(250);

         //legend
        // $pieChart->getOptions()->getLegend()->setPosition('bottom');

         //other options
         $pieChart->getOptions()
           ->setPieHole(0.4)
           ->setColors(array('#ec741b', '#00a7d6', '#e32283', '#bfce00', '#a03488', '9e928f'));

         return $pieChart;
    }

    /**
    * Crée le graphique du temps de travail passé par mois
    *
    * @return PieChart
    */
     public function timeTrackerTempsTravailParMois($arr_data)
     {
        $colChart = new ColumnChart();

        $arr_mois = array(
            1 => 'Jan',
            2 => 'Fév',
            3 => 'Mars',
            4 => 'Avr',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juil',
            8 => 'Août',
            9 => 'Sept',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Déc'
        );

        $arrayDataTable = array(
            ['Mois', 'Temps total passé (en heures)' ]
        );
        foreach($arr_data as $mois => $data){
            $arrayDataTable[] = [
                $arr_mois[$mois], 
                $data, 
            ];
        }

        $colChart->getData()->setArrayToDataTable($arrayDataTable);
        $colChart->getOptions()->setColors(array('#ec741b'));
        $colChart->getOptions()->getLegend()->setPosition('none');

        return $colChart;
    }

    /**
    * Crée le graphique du temps de travail passé par an
    *
    * @return PieChart
    */
     public function timeTrackerTempsTravailParAnnee($arr_data)
     {
        $colChart = new ColumnChart();


        $arrayDataTable = array(
            ['Année', 'Temps total passé (en heures)' ]
        );
        foreach($arr_data as $annee => $data){
            $arrayDataTable[] = [
                strval($annee), 
                $data, 
            ];
        }

        $colChart->getData()->setArrayToDataTable($arrayDataTable);
        $colChart->getOptions()->setColors(array('#ec741b'));
        $colChart->getOptions()->getLegend()->setPosition('none');

        return $colChart;
    }

    /**
    * Crée le graphique du temps commercial par montant de l'action commerciale
    *
    * @return Candlestick Chart
    */
     public function tempsCommercialParMontant($arr_data)
     {
        $chart = new CandlestickChart();

        $chart->getData()->setArrayToDataTable($arr_data, true);
        $chart->getOptions()->getLegend()->setPosition('none');
        $chart->getOptions()->getBar()->setGroupWidth('100%');
        $chart->getOptions()->getCandlestick()->getFallingColor()->setStrokeWidth(0);
        $chart->getOptions()->getCandlestick()->getFallingColor()->setFill('#a52714');
        $chart->getOptions()->getCandlestick()->getRisingColor()->setStrokeWidth(0);
        $chart->getOptions()->getCandlestick()->getRisingColor()->setFill('#0f9d58');
        $chart->getOptions()->setWidth(900);
        $chart->getOptions()->setHeight(500);

        return $chart;
    }
}
