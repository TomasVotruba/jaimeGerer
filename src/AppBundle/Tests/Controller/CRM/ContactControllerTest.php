<?php
namespace AppBundle\Tests\Controller\CRM;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{

	private $client = null;

    public function setUp()
    {
    	$fixtures = $this->loadFixtures(array(
            'AppBundle\DataFixtures\AppFixtures'
        ))->getReferenceRepository();

        $this->loginAs($fixtures->getReference('user-laura'), 'main');
		$this->client = $this->makeClient();
    }

    /**
	 *	Test : affichage fiche contact
	 **/
    public function testContactVoir()
    {
        $crawler = $this->client->request('GET', '/crm/contact/voir/1');
        	
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());	
        $this->assertContains('Geoffroy Murat', $crawler->filter('#compte_details h1')->text());
        $this->assertContains('Directeur', $crawler->filter('#compte_details h1 span.l')->text());
    }

     /**
	 *	Test : importer un fichier de contacts contenant 1 nouveau compte et 1 nouveau contact
	 **/
    public function testContactImportNouveauCompte()
    {
        $crawler = $this->client->request('GET', '/crm/contact/import/upload');
        	
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Importer des contacts', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $form['form[fichier_import]']->upload('web\files\test\crm_import_contact_nouveau.xlsx');
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
       
        $this->assertContains('Valider un fichier', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertCount(1, $crawler->filter('#collapse-comptes-non-existant ul li')); 
        $this->assertContains('A la pêche aux moules moules moules', $crawler->filter('#collapse-comptes-non-existant ul li:first-child')->text()); 
        $this->assertCount(1, $crawler->filter('#collapse-contacts-non-existant ul li'));
        $this->assertContains('Madame Colette', $crawler->filter('#collapse-contacts-non-existant ul li:first-child')->text());
        
        $form = $crawler->filter('#form_valider')->form();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Résultats de l\'import de contacts', $crawler->filter('h1:not(#titre-outil)')->text());
		$this->assertCount(1, $crawler->filter('#collapse-comptes-created ul li'));
		$this->assertContains('A la pêche aux moules moules moules', $crawler->filter('#collapse-comptes-created ul li:first-child')->text());
		$this->assertCount(1, $crawler->filter('#collapse-contacts-created ul li'));
		$this->assertContains('Madame Colette', $crawler->filter('#collapse-contacts-created ul li:first-child')->text());

		$link = $crawler
		    ->filter('#collapse-comptes-created ul li:first-child a')
		    ->eq(0)
		    ->link()
		;
		$crawler = $this->client->click($link);
		$this->assertContains('A la pêche aux moules moules moules', $crawler->filter('h1:not(#titre-outil)')->text());

         
    }

 	// /**
	 // *	Test : téléchargement template Excel pour import de contacts
	 // **/
  //   public function testContactImportDownloadExcelTemplate(){

  //   	$crawler = $this->client->request('GET', '/crm/contact/import/upload');

  //   	$this->assertEquals(200, $this->client->getResponse()->getStatusCode());

  //   	//click on the download link
  //   	$link = $crawler
		//     ->filter('a#btn-download-excel-template')
		//     ->eq(0)
		//     ->link()
		// ;
		// $crawler = $this->client->click($link);

		// $this->assertTrue(
		//     $this->client->getResponse()->headers->contains(
		//         'Content-Disposition',
		//         'attachment'
		//     )
		// );
  //   }

    /* todo : 
    	- test ajout contact
    	- test edition contact
    	- test suppression contact
    */

}
