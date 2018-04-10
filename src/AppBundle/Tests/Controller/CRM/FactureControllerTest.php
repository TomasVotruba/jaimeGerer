<?php
namespace AppBundle\Tests\Controller\CRM;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class FactureControllerTest extends WebTestCase
{

	private $client = null;
    private $fixtures = null;

    public function setUp()
    {
    	$this->fixtures = $this->loadFixtures(array(
            'AppBundle\DataFixtures\AppFixtures'
        ))->getReferenceRepository();

        $this->loginAs($this->fixtures->getReference('user-laura'), 'main');
		$this->client = $this->makeClient();
    }

    /**
     * @todo : 
     * test modifier
     * test supprimer
     * test écriture journal des ventes
     * test si compte comptable ne peut pas être créé automatiquement
     * */

     /**
     *  Test : affichage fiche facture
     **/
    public function testFactureVoir()
    {
        $crawler = $this->client->request('GET', '/crm/facture/voir/2');
            
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());    
        $this->assertContains('Facture', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Formation', $crawler->filter('h2')->text());
    }

    /**
     *  Test : ajouter une facture par le formulaire
    **/
    public function testFactureAjouter(){

        $crawler = $this->client->request('GET', '/crm/facture/ajouter');

        //test : affichage et soumission du formulaire
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créer une facture', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('btn-submit-form');
        $form = $buttonCrawlerNode->form();

        $values = $form->getPhpValues();
        $csrfToken = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('appbundle_crm_facture');
        $values['appbundle_crm_facture']['_token'] = $csrfToken;
        $values['appbundle_crm_facture']['objet'] = 'Conseil diversité';
        $values['appbundle_crm_facture']['dateCreation'] = '10/03/2018';
        $values['appbundle_crm_facture']['dateValidite'] = '10/04/2018';
        $values['appbundle_crm_facture']['compte'] = 2;
        $values['appbundle_crm_facture']['contact'] = 3;
        $values['appbundle_crm_facture']['adresse'] = '110 allée promenade des bords du lac';
        $values['appbundle_crm_facture']['codePostal'] = '73100';
        $values['appbundle_crm_facture']['ville'] = 'Aix les Bains';
        $values['appbundle_crm_facture']['region'] = 'Auvergne-Rhône-Alpes';
        $values['appbundle_crm_facture']['pays'] = 'France';
        $values['appbundle_crm_facture']['analytique'] = $this->fixtures->getReference('analytique-conseil')->getId();
       
        $values['appbundle_crm_facture']['bc_entity'] = $this->fixtures->getReference('bon-commande-2018-001')->getId();
        $values['appbundle_crm_facture']['numBCClient'] = 'POTIRON-BC-123';
        $values['appbundle_crm_facture']['devis'] = $this->fixtures->getReference('devis-2018-001')->getId();
        
        $values['appbundle_crm_facture']['produits'][0]['type'] = $this->fixtures->getReference('type-produit-conseil')->getId();
        $values['appbundle_crm_facture']['produits'][0]['nom'] = 'Journée de conseil';
        $values['appbundle_crm_facture']['produits'][0]['tarifUnitaire'] = 1000;
        $values['appbundle_crm_facture']['produits'][0]['quantite'] = 1;
        $values['appbundle_crm_facture']['produits'][0]['description'] = 'Journée de conseil dans vos locaux';

        $values['appbundle_crm_facture']['taxe'] = 200;
        $values['appbundle_crm_facture']['taxePercent'] = 20;
        $values['appbundle_crm_facture']['totalHT'] = 1000;
      
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());

        //test : création des valeurs de la facture sur la fiche facture
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Conseil diversité', $crawler->filter('h2')->text());
        $this->assertContains('Conseil diversité', $crawler->filter('#facture-objet')->text());
        $this->assertContains('2018-002', $crawler->filter('#facture-num')->text());
        $this->assertContains('10/04/2018', $crawler->filter('#facture-date-validite')->text());
        $this->assertContains('DemoCorp', $crawler->filter('#facture-compte')->text());
        $this->assertContains('Potiron Groschat', $crawler->filter('#facture-contact')->text());
        $this->assertContains('Laura Gilquin', $crawler->filter('#facture-user-creation')->text());
        $this->assertContains('Conseil', $crawler->filter('#facture-analytique')->text());
        $this->assertContains('2018-001', $crawler->filter('#facture-num-bc-interne')->text());
        $this->assertContains('POTIRON-BC-123', $crawler->filter('#facture-num-bc-client')->text());
        $this->assertContains('2018-001', $crawler->filter('#facture-num-devis')->text());
        $this->assertCount(2, $crawler->filter('.produit-collection table tbody tr'));

        //test : vérifier le prix HT, TTC et la TVA
        $this->assertEquals('1000 €', $crawler->filter('#facture-total-ht')->text());
        $this->assertEquals('1200 €', $crawler->filter('#facture-total-ttc')->text());
        $this->assertContains('20%', $crawler->filter('#facture-total-tax-percent')->text());
        $this->assertContains('200.00 €', $crawler->filter('#facture-total-tax-montant')->text());

    }

     /**
     *  Test : exporter facture
     **/
    public function testFactureExporter()
    {
        $crawler = $this->client->request('GET', '/crm/facture/exporter/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'content-type',
                'application/pdf'
            )
        );

    }

     /**
     *  Test : envoyer la facture au client
     **/
    public function testFactureEnvoyer()
    {
        
        $crawler = $this->client->request('GET', '/crm/facture/envoyer/2');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $form['form[addcc]']->tick();

        $this->client->enableProfiler();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());

        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        
        // Test : email envoyé
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Facture : Formation', $message->getSubject());
        $this->assertSame('gilquin@nicomak.eu', key($message->getFrom()));
        $this->assertSame('potiron@democorp.com', key($message->getTo()));
        $this->assertContains('Veuillez trouver ci-joint notre facture.', $message->getBody());

        $crawler = $this->client->followRedirect();
        $this->assertContains('La facture a bien été envoyée.', $crawler->filter('div.alert-success')->text());

    }

}
