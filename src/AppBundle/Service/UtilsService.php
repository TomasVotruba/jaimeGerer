<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class UtilsService extends ContainerAware {


  public function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

  public function getFirstDayOfYear($year)
  {
      return \DateTime::createFromFormat('Y-m-d', $year.'-01-01');
  }

  public function getFirstDayOfCurrentYear()
  {
      $year = date('Y');
      return \DateTime::createFromFormat('Y-m-d', $year.'-01-01');
  }

  public function intToMoney($val){
    return $val/100;
  }

  public function moneyToInt($val){
    return $val*100;
  }

  public function removeSpecialChars($string){
    $specialChars = array(
       'à', 'ä',
       'é', 'è', 'ê', 'ë',
       'ï', 'î',
       'ö', 'ô',
       'ù', 'û', 'ü',
       ' ', '-', '\''
    );

    $normalChars = array(
       'a', 'a',
       'e', 'e', 'e', 'e',
       'i', 'i',
       'o', 'o',
       'u', 'u', 'u',
       '', '', ''
    );

    $string = str_replace($specialChars, $normalChars, $string);
    return strtolower($string);

  }


}
