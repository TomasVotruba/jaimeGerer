<?php

namespace AppBundle\Service;


class ReceiptBankService {

  protected $browser;

  public function __construct(\Buzz\Browser $browser)
  {
    $this->browser = $browser;
  }



}
