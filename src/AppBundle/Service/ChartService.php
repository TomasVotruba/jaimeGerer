<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
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

}
