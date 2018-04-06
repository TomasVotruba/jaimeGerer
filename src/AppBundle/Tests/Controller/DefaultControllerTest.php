<?php
namespace AppBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
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
	 *	Test : index redirige vers page de login si le user n'est pas authentifié 
	 **/
    public function testIndexNotAuthenticated()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    	
    	$this->assertTrue($client->getResponse()->isRedirect());
    	$crawler = $client->followRedirect();
    	
    	$this->assertCount(1, $crawler->filter('input#username'));
    	$this->assertCount(1, $crawler->filter('input#password'));
        
    }

    /**
	 *	Test : index affiche la page d'accueil si le user est authentifié 
	 **/
    public function testIndexUserAuthenticated(){
    
		$crawler = $this->client->request('GET', '/');
    	$this->assertContains('Laura', $crawler->filter('h1')->text());

    }
}