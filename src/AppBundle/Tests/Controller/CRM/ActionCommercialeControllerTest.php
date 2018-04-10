<?php
namespace AppBundle\Tests\Controller\CRM;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ActionCommercialeControllerTest extends WebTestCase
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
     * test sous-traitance
     * test modifier répartition
     * test modifier bon de commande
     * */

     /**
     *  Test : affichage fiche action commerciale
     **/
    public function testActionCommercialeVoir()
    {
        $crawler = $this->client->request('GET', '/crm/action-commerciale/voir/1');
            
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());  
        $this->assertContains('Action commerciale', $crawler->filter('h1:not(#titre-outil)')->text());  
        $this->assertContains('Conseil diversité', $crawler->filter('h2')->text());
    }

    /**
     *  Test : ajouter une action commerciale par le formulaire
    **/
    public function testActionCommercialeAjouter(){

        $crawler = $this->client->request('GET', '/crm/action-commerciale/ajouter');

        //test : affichage et soumission du formulaire
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créer une action commerciale', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('btn-submit-form');
        $form = $buttonCrawlerNode->form();

        $values = $form->getPhpValues();
        $csrfToken = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('appbundle_crm_opportunite');
        $values['appbundle_crm_opportunite']['_token'] = $csrfToken;
        $values['appbundle_crm_opportunite']['nom'] = 'Conseil (test)';
        $values['appbundle_crm_opportunite']['date'] = '01/03/2018';
        $values['appbundle_crm_opportunite']['dateValidite'] = '01/04/2018';
        $values['appbundle_crm_opportunite']['compte'] = 2;
        $values['appbundle_crm_opportunite']['contact'] = 3;
        $values['appbundle_crm_opportunite']['adresse'] = '110 allée promenade des bords du lac';
        $values['appbundle_crm_opportunite']['codePostal'] = '73100';
        $values['appbundle_crm_opportunite']['ville'] = 'Aix les Bains';
        $values['appbundle_crm_opportunite']['region'] = 'Auvergne-Rhône-Alpes';
        $values['appbundle_crm_opportunite']['pays'] = 'France';
        $values['appbundle_crm_opportunite']['type'] = 'Existing Business';
        $values['appbundle_crm_opportunite']['probabilite'] = $this->fixtures->getReference('probabilite-action-co')->getId();
        $values['appbundle_crm_opportunite']['origine'] = $this->fixtures->getReference('origine-appel-entrant')->getId();
        $values['appbundle_crm_opportunite']['analytique'] = $this->fixtures->getReference('analytique-conseil')->getId();
        $values['appbundle_crm_opportunite']['priveOrPublic'] = 'PRIVE';
        
        $values['appbundle_crm_opportunite']['produits'][0]['type'] = $this->fixtures->getReference('type-produit-conseil')->getId();
        $values['appbundle_crm_opportunite']['produits'][0]['nom'] = 'Séminaire';
        $values['appbundle_crm_opportunite']['produits'][0]['tarifUnitaire'] = 1000;
        $values['appbundle_crm_opportunite']['produits'][0]['quantite'] = 2;
        $values['appbundle_crm_opportunite']['produits'][0]['description'] = 'Journées animées par nos consultants';

        $values['appbundle_crm_opportunite']['produits'][1]['type'] = $this->fixtures->getReference('type-produit-conseil')->getId();
        $values['appbundle_crm_opportunite']['produits'][1]['nom'] = 'Restitution';
        $values['appbundle_crm_opportunite']['produits'][1]['tarifUnitaire'] = 1000;
        $values['appbundle_crm_opportunite']['produits'][1]['quantite'] = 1;
        $values['appbundle_crm_opportunite']['produits'][1]['description'] = 'Restitution par nos consultants';

        $values['appbundle_crm_opportunite']['taxe'] = 600;
        $values['appbundle_crm_opportunite']['taxePercent'] = 20;
        $values['appbundle_crm_opportunite']['totalHT'] = 3000;
      
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());

        //test : création des valeurs de l'action commerciale sur la fiche action commerciale
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Conseil (test)', $crawler->filter('h2')->text());
        $this->assertContains('Conseil (test)', $crawler->filter('#action-commerciale-objet')->text());
        $this->assertContains('01/04/2018', $crawler->filter('#action-commerciale-date-validite')->text());
        $this->assertContains('DemoCorp', $crawler->filter('#action-commerciale-compte')->text());
        $this->assertContains('Potiron Groschat', $crawler->filter('#action-commerciale-contact')->text());
        $this->assertContains('Laura Gilquin', $crawler->filter('#action-commerciale-user-creation')->text());
        $this->assertContains('Conseil', $crawler->filter('#action-commerciale-analytique')->text());
        $this->assertContains('Privé', $crawler->filter('#action-commerciale-priveOrPublic')->text());
        $this->assertCount(4, $crawler->filter('.produit-collection table tbody tr'));

        //test : vérifier le prix HT, TTC et la TVA
        $this->assertEquals('3000 €', $crawler->filter('#total-ht')->text());
        $this->assertEquals('3600 €', $crawler->filter('#total-ttc')->text());
        $this->assertContains('20%', $crawler->filter('#total-tax-percent')->text());
        $this->assertContains('600.00 €', $crawler->filter('#total-tax-montant')->text());

    }

     /**
     *  Test : perdre action commerciale
     **/
    public function testActionCommercialePerdre()
    {
        $crawler = $this->client->request('GET', '/crm/action-commerciale/perdre/1');

        $this->assertTrue($this->client->getResponse()->isRedirection());
        
        $crawler = $this->client->request('GET', '/crm/action-commerciale/voir/1');  
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());    
        $this->assertContains('Conseil diversité', $crawler->filter('h2')->text());
        $this->assertContains('Action commerciale perdue', $crawler->filter('h2 small.red')->text());

        //test : sur la fiche action commerciale, on ne peut ni envoyer, ni convertir en facture
        $this->assertCount(0, $crawler->filter('#btn-send-devis'));
        $this->assertCount(0, $crawler->filter('#btn-convertir-devis'));
        
    }

     /**
     *  Test : gagner action commerciale
     **/
    public function testActionCommercialeGagner()
    {
        $crawler = $this->client->request('GET', '/crm/action-commerciale/gagner/1');
        
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Action commerciale gagnée : Conseil diversité', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('appbundle_crm_opportunite_won_bon_commande_submit');
        $form = $buttonCrawlerNode->form();
        $values = $form->getPhpValues();
        $values['appbundle_crm_opportunite_won_bon_commande']['bonsCommande'][0]['num'] = '2018-002';
        $values['appbundle_crm_opportunite_won_bon_commande']['bonsCommande'][0]['montantMonetaire'] = 1000;
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $buttonCrawlerNode = $crawler->selectButton('appbundle_crm_opportunite_won_repartition_submit');
        $form = $buttonCrawlerNode->form();
        $values = $form->getPhpValues();
        $csrfToken = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('appbundle_crm_opportunite_won_repartition');
        $values['appbundle_crm_opportunite_won_repartition']['_token'] = $csrfToken;

        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][0]['date']['day'] = 1;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][0]['date']['month'] = 3;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][0]['date']['year'] = 2018;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][0]['montantMonetaire'] = 800;

        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][1]['date']['day'] = 1;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][1]['date']['month'] = 4;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][1]['date']['year'] = 2018;
        $values['appbundle_crm_opportunite_won_repartition']['opportuniteRepartitions'][1]['montantMonetaire'] = 200;

        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Conseil diversité', $crawler->filter('h2')->text());
        $this->assertContains('Action commerciale gagnée', $crawler->filter('h2 small.green')->text());
        
        $this->assertCount(1, $crawler->filter('#table-bons-commande tbody tr'));
        $this->assertContains('2018-002', $crawler->filter('#table-bons-commande tbody tr:first-child td:first-child')->text());
        $this->assertContains('1 000,00 €', $crawler->filter('#table-bons-commande tbody tr:first-child td:nth-child(2)')->text());

        $this->assertCount(2, $crawler->filter('#table-repartition tbody tr'));
        $this->assertContains('mars 2018', $crawler->filter('#table-repartition tbody tr:first-child td:first-child')->text());
        $this->assertContains('800,00 €', $crawler->filter('#table-repartition tbody tr:first-child td:nth-child(2)')->text());
        $this->assertContains('avr. 2018', $crawler->filter('#table-repartition tbody tr:nth-child(2) td:first-child')->text());
        $this->assertContains('200,00 €', $crawler->filter('#table-repartition tbody tr:nth-child(2) td:nth-child(2)')->text());
  
    }

     /**
     *  Test : exporter devis de l'action commerciale
     **/
    public function testActionCommercialeExporter()
    {
        $crawler = $this->client->request('GET', '/crm/action-commerciale/exporter/1');

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'content-type',
                'application/pdf'
            )
        );

    }

     /**
     *  Test : envoyer le devis de l'action commerciale au client
     **/
    public function testActionCommercialeEnvoyer()
    {
        
        $crawler = $this->client->request('GET', '/crm/action-commerciale/envoyer/1');
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
        $this->assertSame('Devis : Conseil diversité', $message->getSubject());
        $this->assertSame('gilquin@nicomak.eu', key($message->getFrom()));
        $this->assertSame('potiron@democorp.com', key($message->getTo()));
        $this->assertContains('Veuillez trouver ci-joint notre devis.', $message->getBody());

        $crawler = $this->client->followRedirect();
        $this->assertContains('Le devis a bien été envoyé.', $crawler->filter('div.alert-success')->text());

    }

     /**
     *  Test : convertir action commerciale en facture
     **/
    public function testActionCommercialeConvertir()
    {
        $crawler = $this->client->request('GET', '/crm/action-commerciale/convertir/1');

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Facture', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Conseil diversité', $crawler->filter('h2')->text());

        $this->assertContains('2018-002', $crawler->filter('#facture-num')->text());
        $this->assertContains('DemoCorp', $crawler->filter('#facture-compte')->text());
        $this->assertContains('Potiron Groschat', $crawler->filter('#facture-contact')->text());
        $this->assertContains('Conseil', $crawler->filter('#facture-analytique')->text());
        $this->assertContains('2018-001', $crawler->filter('#facture-num-bc-interne')->text());

        $this->assertContains('1000 €', $crawler->filter('#facture-total-ht')->text());
        $this->assertContains('1200 €', $crawler->filter('#facture-total-ttc')->text());
        $this->assertContains('20%', $crawler->filter('#facture-total-tax-percent')->text());
        $this->assertContains('200.00 €', $crawler->filter('#facture-total-tax-montant')->text());

    }


}
